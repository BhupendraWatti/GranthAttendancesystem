<?php
header('Content-Type: text/plain');

$url = "https://api.etimeoffice.com/api/DownloadPunchData";
$username = "sonali_verma";
$password = "Happydiwali@202";
$companyCode = "granthinfotech";

$headers = [
    "Authorization: Basic " . base64_encode($username . ":" . $password),
    "Companycode: " . $companyCode,
    "Content-Type: application/json",
    "Accept: application/json"
];

// Test 1: Standard URL encoded params
$qs1 = http_build_query([
    'Empcode' => 'ALL',
    'FromDate' => date('d/m/Y', strtotime('-1 days')),
    'ToDate' => date('d/m/Y')
]);

// Test 2: Unencoded params string (eTimeOffice ASP.NET parser sometimes breaks on heavily encoded slashes)
$qs2 = "Empcode=ALL&FromDate=" . date('d/m/Y', strtotime('-1 days')) . "&ToDate=" . date('d/m/Y');

// Test 3: Standard Y-m-d format
$qs3 = http_build_query([
    'Empcode' => 'ALL',
    'FromDate' => date('Y-m-d', strtotime('-1 days')),
    'ToDate' => date('Y-m-d')
]);

// Test 4: M/D/YYYY format string without leading zeros
$qs4 = "Empcode=ALL&FromDate=" . date('n/j/Y', strtotime('-1 days')) . "&ToDate=" . date('n/j/Y');

// Test 5: No params at all
$qs5 = "";

$tests = [
    "Encoded format d/m/Y" => $qs1,
    "Unencoded format d/m/Y" => $qs2,
    "Encoded format Y-m-d" => $qs3,
    "Format n/j/Y" => $qs4,
    "No params" => $qs5,
];

foreach ($tests as $name => $qs) {
    echo "=== Testing GET: $name ===\n";
    $testUrl = $url;
    if ($qs) $testUrl .= '?' . $qs;
    echo "URL: $testUrl\n";
    
    $ch = curl_init($testUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: $httpCode | Length: " . strlen((string)$response) . "\n";
    
    // Always print if we finally get 200, otherwise just snippet if it's new
    if ($httpCode == 200 && strlen((string)$response) > 0) {
        echo substr((string)$response, 0, 500) . "\n";
    } elseif ($httpCode !== 500 && strlen((string)$response) > 0) {
        echo substr((string)$response, 0, 100) . "...\n";
    }
    echo "\n";
}
