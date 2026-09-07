<?php
$lines = file('resources/js/app.js');
foreach ($lines as $num => $line) {
    if (strpos($line, 'renderLines') !== false) {
        echo ($num + 1) . ": " . trim($line) . "\n";
    }
}
