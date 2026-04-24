<?php
header('Content-Type: text/plain');

$endpoints = [
    "https://api.etimeoffice.com/api/DownloadPunchData",
    "https://api.etimeoffice.com/api/DownloadPunchDataMCID",
    "https://api.etimeoffice.com/api/DownloadInOutPunchData",
    "https://api.etimeoffice.com/api/DownloadLastPunchData"
];

$username = "sonali_verma";
$password = "Happydiwali@202";
$companyCode = "granthinfotech";

$payloadArray = [
    'Empcode' => 'ALL',
    'FromDate' => date('d/m/Y', strtotime('-1 months')),
    'ToDate' => date('d/m/Y')
];
$payload = json_encode($payloadArray);

$headers = [
    "Authorization: Basic " . base64_encode($username . ":" . $password),
    "Companycode: " . $companyCode,
    "Content-Type: application/json",
    "Accept: application/json"
];

echo "=== TESTING ALL ETHIMEOFFICE ENDPOINTS ===\n\n";

foreach ($endpoints as $url) {
    echo "Testing: $url\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "HTTP Status: $httpCode\n";
    if (strlen($response) > 500) {
        echo "Response: (truncated) " . substr($response, 0, 500) . "...\n";
    } else {
        echo "Response: " . $response . "\n";
    }
    echo "----------------------------------------\n\n";
}
