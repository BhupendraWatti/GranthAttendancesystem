<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;

/**
 * ApiService — eTimeOffice API Client
 * 
 * Handles all communication with the eTimeOffice API including:
 * - Authentication (Basic Auth with Base64)
 * - Date format handling
 * - Retry logic with exponential backoff
 * - Error handling and logging
 */
class ApiService
{
    private string $baseUrl;
    private string $username;
    private string $password;
    /** eTimeOffice tenant code; also sent as `Companycode` header (see local api_debug.php / project overview). */
    private string $companyCode;
    private int $maxRetries = 3;
    private CURLRequest $client;
    private bool $useMockInOut;

    public function __construct()
    {
        $this->baseUrl     = rtrim(env('ETIME_BASE_URL', 'https://api.etimeoffice.com/api'), '/');
        $this->companyCode = (string) env('ETIME_COMPANY_CODE', '');
        $rawUsername       = env('ETIME_USERNAME', '');
        // Basic Auth username: "companyCode:loginName" when company is set (eTimeOffice); Companycode header also sent.
        $this->username    = $this->companyCode !== '' ? "{$this->companyCode}:{$rawUsername}" : $rawUsername;
        $this->password    = env('ETIME_PASSWORD', '');
        $this->client      = \Config\Services::curlrequest();
        $this->useMockInOut = filter_var(env('ETIME_USE_MOCK_INOUT', false), FILTER_VALIDATE_BOOL);
    }

    /**
     * Override credentials at runtime (used by AuthController for live login).
     * Does NOT affect the default .env-based credentials used by SyncController.
     *
     * @param string $username eTimeOffice username for Basic Auth (often "companyCode:loginName" when company is set)
     * @param string $password eTimeOffice password
     */
    public function setCredentials(string $username, string $password): void
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Verify credentials against the live eTimeOffice API.
     *
     * Strategy: make a real GET request and check the HTTP status:
     *   - 401 / 403         → credentials INVALID (eTimeOffice rejected them)
     *   - 200, 500, etc.    → credentials VALID   (server processed the request;
     *                         non-2xx may be data errors, not auth errors)
     *   - network exception → treat as failure (can't reach server)
     *
     * We use empcode=ALL for today-only to avoid the IndexOutOfRange crash
     * that eTimeOffice throws when it gets an unknown empcode like AUTH_PING.
     *
     * @return bool true if eTimeOffice does NOT return 401/403
     */
    public function verifyCredentials(): bool
    {
        $today  = date('d/m/Y');
        $url    = "{$this->baseUrl}/DownloadPunchData";
        $params = [
            'Empcode'  => 'ALL',
            'FromDate' => $today . '_00:00',
            'ToDate'   => $today . '_01:00',   // 1-hour window = minimal data
        ];

        $headers = [
            'Authorization' => $this->getAuthHeader(),
            'Accept'        => 'application/json',
        ];
        if ($this->companyCode !== '') {
            $headers['Companycode'] = $this->companyCode;
        }

        $options = [
            'headers'     => $headers,
            'timeout'     => 20,
            'verify'      => (bool) env('ETIME_SSL_VERIFY', false),
            'http_errors' => false,   // Don't throw on 4xx/5xx — we need the status code
        ];

        try {
            $query = http_build_query($params);
            $query = str_replace(['%2F', '%3A', '%24'], ['/', ':', '$'], $query);
            $response   = $this->client->request('GET', $url . '?' . $query, $options);
            $statusCode = $response->getStatusCode();

            log_message('info', "[ApiService::verifyCredentials] HTTP {$statusCode} for user: {$this->username}");

            // 401 = Unauthorized, 403 = Forbidden → bad credentials
            if ($statusCode === 401 || $statusCode === 403) {
                return false;
            }

            // Any other response (200, 500 from data issues) = auth passed
            return true;

        } catch (\Exception $e) {
            log_message('error', "[ApiService::verifyCredentials] Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Build the Basic Auth header
     */
    private function getAuthHeader(): string
    {
        // eTimeOffice strictly expects Basic Auth in the format:
        // "CompanyCode:Username:Password:true\n"
        // $this->username already includes the "CompanyCode:" prefix when configured.
        $credentials = $this->username . ':' . $this->password . ":true\n";
        return 'Basic ' . base64_encode($credentials);
    }

    /**
     * Get default HTTP options for all requests
     */
    private function getDefaultOptions(): array
    {
        $headers = [
            'Authorization' => $this->getAuthHeader(),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
        if ($this->companyCode !== '') {
            $headers['Companycode'] = $this->companyCode;
        }

        return [
            'headers'     => $headers,
            'timeout'     => 30,
            'verify'      => (bool) env('ETIME_SSL_VERIFY', false),
            // Required: default CURLRequest uses CURLOPT_FAILONERROR; HTTP 500 would throw (curl 22)
            // before we can read the response body for logging or handle fallbacks in SyncService.
            'http_errors' => false,
        ];
    }

    /**
     * Download punch data for a date range
     * 
     * API: DownloadPunchData?Empcode=ALL&FromDate=DD/MM/YYYY_HH:MM&ToDate=DD/MM/YYYY_HH:MM
     *
     * @param string $fromDate Start date (Y-m-d format, will be converted)
     * @param string $toDate End date (Y-m-d format, will be converted)
     * @param string $empCode Employee code or 'ALL'
     * @return array API response data
     */
    public function downloadPunchData(string $fromDate, string $toDate, string $empCode = 'ALL'): array
    {
        $from = $this->formatDateTimeForApi($fromDate, '00:00');
        $to   = $this->formatDateTimeForApi($toDate, '23:59');

        $url = "{$this->baseUrl}/DownloadPunchData";
        $params = [
            'Empcode'  => $empCode,
            'FromDate' => $from,
            'ToDate'   => $to,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Download latest punch data since last sync
     * 
     * API: DownloadLastPunchData?Empcode=ALL&LastRecord=MMYYYY$NNNNNNNN
     *
     * @param string $lastRecordId The last record ID from previous sync
     * @param string $empCode Employee code or 'ALL'
     * @return array API response data
     */
    public function downloadLastPunchData(string $lastRecordId, string $empCode = 'ALL'): array
    {
        $url = "{$this->baseUrl}/DownloadLastPunchData";
        // LastRecord is MMYYYY$NNNNNNNN with a literal '$'. http_build_query encodes '$' as %24 and
        // eTimeOffice returns HTTP 500; build this query without encoding the dollar.
        $queryString = 'Empcode=' . rawurlencode($empCode) . '&LastRecord=' . $lastRecordId;

        return $this->makeRequest($url, [], $queryString);
    }

    /**
     * Download punch data with Machine/Controller ID
     * 
     * API: DownloadPunchDataMCID?Empcode=ALL&FromDate=DD/MM/YYYY_HH:MM&ToDate=DD/MM/YYYY_HH:MM
     *
     * @param string $fromDate Start date (Y-m-d format)
     * @param string $toDate End date (Y-m-d format)
     * @param string $empCode Employee code or 'ALL'
     * @return array API response data
     */
    public function downloadPunchDataMCID(string $fromDate, string $toDate, string $empCode = 'ALL'): array
    {
        $from = $this->formatDateTimeForApi($fromDate, '00:00');
        $to   = $this->formatDateTimeForApi($toDate, '23:59');

        $url = "{$this->baseUrl}/DownloadPunchDataMCID";
        $params = [
            'Empcode'  => $empCode,
            'FromDate' => $from,
            'ToDate'   => $to,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Download In/Out punch data (date-only params; used as SyncService fallback when DownloadPunchData fails)
     *
     * API: DownloadInOutPunchData?Empcode=ALL&FromDate=DD/MM/YYYY&ToDate=DD/MM/YYYY
     *
     * @param string $fromDate Start date (Y-m-d format)
     * @param string $toDate End date (Y-m-d format)
     * @param string $empCode Employee code or 'ALL'
     * @return array API response data
     */
    public function downloadInOutPunchData(string $fromDate, string $toDate, string $empCode = 'ALL'): array
    {
        $mocked = $this->readMockInOutResponse();
        if ($mocked !== null) {
            return $mocked;
        }

        $from = $this->formatDateForApi($fromDate);
        $to   = $this->formatDateForApi($toDate);

        $url = "{$this->baseUrl}/DownloadInOutPunchData";
        $params = [
            'Empcode'  => $empCode,
            'FromDate' => $from,
            'ToDate'   => $to,
        ];

        return $this->makeRequest($url, $params);
    }

    /**
     * Read local mock payload only when explicitly enabled by env.
     */
    private function readMockInOutResponse(): ?array
    {
        if (!$this->useMockInOut) {
            return null;
        }

        $mockFilePath = dirname(FCPATH, 2) . '/response.json';
        if (!file_exists($mockFilePath)) {
            log_message('warning', '[ApiService] ETIME_USE_MOCK_INOUT is enabled but response.json was not found');
            return null;
        }

        $mockData = json_decode(file_get_contents($mockFilePath), true);
        if (!is_array($mockData)) {
            log_message('warning', '[ApiService] ETIME_USE_MOCK_INOUT is enabled but response.json is invalid JSON');
            return null;
        }

        log_message('info', '[ApiService] MOCK INTERCEPT ACTIVE - Using local response.json because ETIME_USE_MOCK_INOUT=true');
        return [
            'success' => true,
            'data'    => $mockData,
            'status'  => 200,
        ];
    }

    /**
     * Make an HTTP GET request with retry logic
     *
     * @param string          $url                 Base URL without query
     * @param array           $params              Query key/value (uses http_build_query + eTimeOffice decodes)
     * @param string|null     $queryStringOverride Full query string without leading '?'; skips http_build_query
     */
    private function makeRequest(string $url, array $params = [], ?string $queryStringOverride = null): array
    {
        $options = $this->getDefaultOptions();
        if ($queryStringOverride !== null) {
            $queryString = $queryStringOverride;
        } else {
            $queryString = http_build_query($params);
            // CRITICAL FIX: The older eTimeOffice ASP.NET API crashes with HTTP 500 if the slashes
            // in dates are URL-encoded to %2F. We MUST send them as literal slashes in the query string.
            $queryString = str_replace(['%2F', '%3A', '%24'], ['/', ':', '$'], $queryString);
        }
        $fullUrl = $url . '?' . $queryString;

        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            $attempt++;

            try {
                $attemptTag = $queryStringOverride !== null ? ' [LastPunch-rawQS]' : '';
                log_message('info', "[ApiService] Attempt {$attempt}: GET {$fullUrl}{$attemptTag}");

                $response = $this->client->request('GET', $fullUrl, $options);
                $statusCode = $response->getStatusCode();
                $body = $response->getBody();

                log_message('info', "[ApiService] Response status: {$statusCode}");
                log_message('debug', "[ApiService] Response body: " . substr($body, 0, 500));
                if ($statusCode >= 400) {
                    // Visible at default log threshold — eTimeOffice often returns HTML/JSON with the real error.
                    log_message('warning', '[ApiService] HTTP ' . $statusCode . ' body (truncated): ' . substr($body, 0, 1200));
                }

                if ($statusCode >= 200 && $statusCode < 300) {
                    $decoded = json_decode($body, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Some APIs return XML or other formats
                        log_message('warning', "[ApiService] Non-JSON response, returning raw body");
                        return ['raw' => $body, 'status' => $statusCode];
                    }

                    return [
                        'success' => true,
                        'data'    => $decoded,
                        'status'  => $statusCode,
                    ];
                }

                $lastError = "HTTP {$statusCode}: {$body}";
                log_message('error', "[ApiService] Request failed: {$lastError}");
                
                // Do not retry on 500 Internal Server Errors or 4xx Client Errors
                if ($statusCode >= 400 && $statusCode <= 599) {
                    throw new \Exception("The requested URL returned error: {$statusCode}");
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                log_message('error', "[ApiService] Exception on attempt {$attempt}: {$lastError}");
                
                // Do not retry if the server explicitly returned an error status (like 500) or timed out
                if (strpos($lastError, '500') !== false || strpos($lastError, '404') !== false || strpos($lastError, '401') !== false || strpos($lastError, '403') !== false || strpos($lastError, 'timeout') !== false) {
                    break;
                }
            }

            // Exponential backoff: 1s, 2s, 4s
            if ($attempt < $this->maxRetries) {
                $sleepSeconds = pow(2, $attempt - 1);
                sleep($sleepSeconds);
            }
        }

        log_message('critical', "[ApiService] All {$this->maxRetries} attempts failed for: {$fullUrl}");

        return [
            'success' => false,
            'error'   => $lastError,
            'status'  => 0,
        ];
    }

    /**
     * Convert Y-m-d to DD/MM/YYYY_HH:MM format
     */
    private function formatDateTimeForApi(string $date, string $time = '10:00'): string
    {
        $dt = new \DateTime($date);
        return $dt->format('d/m/Y') . '_' . $time;
    }

    /**
     * Convert Y-m-d to DD/MM/YYYY format
     */
    private function formatDateForApi(string $date): string
    {
        $dt = new \DateTime($date);
        return $dt->format('d/m/Y');
    }

    /**
     * Generate a LastRecord ID from the current date
     * Format: MMYYYY$NNNNNNNN (e.g., 042026$00000002)
     */
    public function generateLastRecordId(?string $monthYear = null, int $recordNumber = 1): string
    {
        if ($monthYear === null) {
            $monthYear = date('mY');
        }
        return $monthYear . '$' . str_pad($recordNumber, 8, '0', STR_PAD_LEFT);
    }
}
