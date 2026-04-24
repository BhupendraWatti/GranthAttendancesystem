<?php
header('Content-Type: text/plain');

$urls = [
    "https://api.etimeoffice.com/api/DownloadPunchData",
    "https://etimeoffice.com/api/DownloadPunchData",
    "https://api.teamoffice.in/api/DownloadPunchData"
];

$username = "sonali_verma";
$password = "Happydiwali@202";
$companyCode = "granthinfotech";

$fromDate = date('d/m/Y', strtotime('-1 months'));
$toDate = date('d/m/Y');
$payloadArray = ['Empcode' => 'ALL', 'FromDate' => $fromDate, 'ToDate' => $toDate];
$payloadJson = json_encode($payloadArray);
$queryString = http_build_query($payloadArray);

$headers = [
    "Authorization: Basic " . base64_encode($username . ":" . $password),
    "Companycode: " . $companyCode,
    "Content-Type: application/json",
    "Accept: application/json"
];

foreach ($urls as $url) {
    echo "=== Testing $url ===\n";
    
    // Test POST
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadJson);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "[POST] HTTP Status: $httpCode | Length: " . strlen((string)$response) . "\n";

    // Test GET
    $getUrl = $url . '?' . $queryString;
    $ch = curl_init($getUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "[GET] HTTP Status: $httpCode | Length: " . strlen((string)$response) . "\n";
    echo "\n";
}
