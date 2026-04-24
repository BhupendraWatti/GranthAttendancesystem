<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$app = \Config\Services::codeigniter();
$app->initialize();

header('Content-Type: text/plain');

$apiService = new \App\Services\ApiService();
$syncService = new \App\Services\SyncService();
$normalizationService = new \App\Services\NormalizationService();

echo "=== TEST 1: Raw API Call to DownloadInOutPunchData ===\n";
$fromDate = '2026-04-01';
$toDate = '2026-04-24';
$rawApiResult = $apiService->downloadInOutPunchData($fromDate, $toDate);

if (!isset($rawApiResult['data'])) {
    echo "FAILED TO FETCH API DATA. Check network/response.\n";
    print_r($rawApiResult);
} else {
    echo "API fetch successful. Data array length: " . (is_array($rawApiResult['data']) ? count($rawApiResult['data']) : 'not array') . "\n";
    if (isset($rawApiResult['data']['InOutPunchData'])) {
        echo "Found 'InOutPunchData' key. Number of raw records: " . count($rawApiResult['data']['InOutPunchData']) . "\n";
    }
}

echo "\n=== TEST 2: Normalization ===\n";
if (isset($rawApiResult['data'])) {
    $normalized = $normalizationService->normalizePunchData($rawApiResult['data'], 'DownloadInOutPunchData');
    echo "Normalized " . count($normalized) . " total IN/OUT punches.\n";
    if (count($normalized) > 0) {
        echo "Sample normalized record:\n";
        print_r($normalized[0]);
    }
}

echo "\n=== TEST 3: Full Range Sync via SyncService ===\n";
// Using a slightly different date to bypass "already running" lock if any
$syncResult = $syncService->runFullRange('2026-04-01', '2026-04-22');
print_r($syncResult);
