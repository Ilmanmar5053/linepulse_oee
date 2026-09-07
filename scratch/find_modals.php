<?php
$content = file_get_contents(__DIR__ . '/../resources/js/app.js');
$lines = explode("\n", $content);

echo "=== OCCURRENCES OF MODAL BACKDROPS (fixed inset-0) ===\n";
foreach ($lines as $i => $line) {
    if (strpos($line, 'fixed inset-0') !== false) {
        $lineNum = $i + 1;
        echo "Line {$lineNum}: " . trim(substr($line, 0, 140)) . "\n";
    }
}
