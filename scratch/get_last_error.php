<?php
$content = file_get_contents('storage/logs/laravel.log');
preg_match_all('/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*?(?=\[\d{4}-\d{2}-\d{2}|$)/s', $content, $matches);
if (!empty($matches[0])) {
    $lastError = end($matches[0]);
    echo "LAST ERROR:\n" . substr($lastError, 0, 1500) . "\n";
}
