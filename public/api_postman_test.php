<?php
header('Content-Type: text/plain');

$url = "https://api.etimeoffice.com/api/DownloadInOutPunchData?Empcode=ALL&FromDate=01/04/2026&ToDate=22/04/2026";
$username = "sonali_verma";
$password = "Happydiwali@202";
$companyCode = "granthinfotech";

$testCases = [
    "CompanyCode inside Basic Auth (Current CI4 logic)" => [
        "Authorization: Basic " . base64_encode("{$companyCode}:{$username}:{$password}"),
        "Content-Type: application/json"
    ],
    "CompanyCode as separate Header (My old debug test)" => [
        "Authorization: Basic " . base64_encode("{$username}:{$password}"),
        "Companycode: {$companyCode}",
        "Content-Type: application/json"
    ],
    "CompanyCode as X-CompanyCode" => [
        "Authorization: Basic " . base64_encode("{$username}:{$password}"),
        "X-CompanyCode: {$companyCode}",
        "Content-Type: application/json"
    ],
    "Postman Default Headers with Company in Auth" => [
        "Authorization: Basic " . base64_encode("{$companyCode}:{$username}:{$password}"),
        "Content-Type: application/json",
        "User-Agent: PostmanRuntime/7.29.2",
        "Accept: */*",
        "Connection: keep-alive"
    ],
    "Postman Headers with Company separate" => [
        "Authorization: Basic " . base64_encode("{$username}:{$password}"),
        "Companycode: {$companyCode}",
        "Content-Type: application/json",
        "User-Agent: PostmanRuntime/7.29.2",
        "Accept: */*",
        "Connection: keep-alive"
    ]
];

foreach ($testCases as $name => $headers) {
    echo "=== TEST: $name ===\n";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Status: $httpCode\n";
    if ($httpCode == 200) {
        echo "SUCCESS! " . substr((string)$response, 0, 150) . "...\n\n";
    } else {
        echo "FAIL\n\n";
    }
}
