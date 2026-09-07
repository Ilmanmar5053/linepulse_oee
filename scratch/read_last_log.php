<?php
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (!file_exists($logFile)) {
    echo "Log file not found.\n";
    exit;
}

$content = file_get_contents($logFile);
$matches = [];
preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] local\.ERROR:.*/', $content, $matches);
$lastErrors = array_slice($matches[0], -10);

foreach ($lastErrors as $err) {
    echo $err . "\n\n";
}
