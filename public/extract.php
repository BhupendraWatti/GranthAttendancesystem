<?php
header('Content-Type: text/plain');

$filePath = dirname(__DIR__, 2) . '/API1.xls';
if (!file_exists($filePath)) {
    echo "File not found: " . $filePath . "\n";
    exit;
}

$content = file_get_contents($filePath);
// Strip null bytes so UTF-16 encoded ASCII strings can be matched with normal Regex
$searchableContent = str_replace("\x00", "", $content);

echo "========= API1.xls EXTRACTED ENDPOINTS =========\n\n";

echo "--- FULL URLs ---\n";
preg_match_all('/https?:\/\/[a-zA-Z0-9\.\/\-_]+/', $searchableContent, $urls);
$uniqueUrls = array_unique($urls[0] ?? []);
if (empty($uniqueUrls)) {
    echo "None found.\n";
} else {
    foreach ($uniqueUrls as $url) {
        echo $url . "\n";
    }
}

echo "\n--- API PATHS ---\n";
preg_match_all('/[a-zA-Z0-9]*\/api\/[a-zA-Z0-9\/\-_]+/', $searchableContent, $paths);
$uniquePaths = array_unique($paths[0] ?? []);
if (empty($uniquePaths)) {
    echo "None found.\n";
} else {
    foreach ($uniquePaths as $path) {
        echo $path . "\n";
    }
}

echo "\n--- 'Download' References ---\n";
preg_match_all('/[a-zA-Z0-9\/_]*Download[a-zA-Z0-9\/_]*/', $searchableContent, $downloads);
$uniqueDownloads = array_unique($downloads[0] ?? []);
foreach ($uniqueDownloads as $d) {
    echo $d . "\n";
}
