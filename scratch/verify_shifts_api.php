<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Shift;

echo "=== SHIFT MANAGEMENT DATABASE PARAMETERS ===\n";
$shifts = Shift::all();
foreach ($shifts as $s) {
    $startStr = substr($s->start_time, 0, 5);
    $endStr = substr($s->end_time, 0, 5);

    $startParts = explode(':', $startStr);
    $endParts = explode(':', $endStr);

    $startMins = intval($startParts[0]) * 60 + intval($startParts[1] ?? 0);
    $endMins = intval($endParts[0]) * 60 + intval($endParts[1] ?? 0);

    if ($endMins <= $startMins) {
        $endMins += 24 * 60; // Overnight shift
    }

    $totalDuration = $endMins - $startMins;
    $breakMins = intval($s->break_duration_minutes ?? 60);
    $plannedTime = max(0, $totalDuration - $breakMins);

    echo sprintf(
        "Shift ID: %d | Name: %-10s | Working Hours: %s - %s | Total: %d mins (%.1fh) | Break: %d mins | Planned Time: %d mins (%.1fh)\n",
        $s->id,
        $s->name,
        $startStr,
        $endStr,
        $totalDuration,
        $totalDuration / 60,
        $breakMins,
        $plannedTime,
        $plannedTime / 60
    );
}
