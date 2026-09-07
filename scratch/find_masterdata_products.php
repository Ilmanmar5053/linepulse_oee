<?php
$content = file_get_contents(__DIR__ . '/../resources/js/app.js');
$lines = explode("\n", $content);

echo "=== OCCURRENCES OF masterData.products ===\n";
foreach ($lines as $i => $line) {
    if (stripos($line, 'masterData.products') !== false) {
        $lineNum = $i + 1;
        echo "Line {$lineNum}: " . trim(substr($line, 0, 120)) . "\n";
    }
}
