<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Downtime;

echo "=== COLUMNS IN DOWNTIMES TABLE ===\n";
$columns = Schema::getColumnListing('downtimes');
print_r($columns);

echo "\n=== ALL DOWNTIMES IN DATABASE ===\n";
$downtimes = Downtime::with(['machine', 'productionLine', 'downtimeReason'])->get();
foreach ($downtimes as $dt) {
    echo "ID: {$dt->id} | Machine: " . ($dt->machine?->code ?? $dt->machine_id) . " | Reason: " . ($dt->downtimeReason?->name ?? 'N/A') . " | Duration: {$dt->duration_minutes}m | Action/Description: {$dt->description} | Action Taken: " . ($dt->action_taken ?? 'N/A') . " | Root Cause: " . ($dt->root_cause ?? 'N/A') . " | PIC: " . ($dt->operator_name ?? $dt->pic ?? 'N/A') . "\n";
}
