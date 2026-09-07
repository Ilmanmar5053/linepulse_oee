<?php
$content = file_get_contents(__DIR__ . '/../resources/js/app.js');
$lines = explode("\n", $content);

echo "=== OCCURRENCES OF PRODUCT SELECTION / FILTERING ===\n";
foreach ($lines as $i => $line) {
    if (stripos($line, 'product_id') !== false || stripos($line, 'productSelect') !== false || stripos($line, 'filterProduct') !== false || stripos($line, 'showProductionModal') !== false || stripos($line, 'showEditProductionRecordModal') !== false || stripos($line, 'showEditDowntimeModal') !== false) {
        $lineNum = $i + 1;
        echo "Line {$lineNum}: " . trim(substr($line, 0, 120)) . "\n";
    }
}
