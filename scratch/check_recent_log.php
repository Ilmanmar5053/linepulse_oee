<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;

// Check recent log entries
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    $content = file_get_contents($logPath);
    $lines = explode("\n", $content);
    $recent = array_slice($lines, -60);
    echo "Recent Laravel Log (last 60 lines):\n";
    echo implode("\n", $recent) . "\n";
} else {
    echo "No log file found.\n";
}
