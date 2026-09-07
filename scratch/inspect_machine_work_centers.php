<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;
use App\Models\WorkCenter;
use App\Models\ProductionLine;

echo "=== WORK CENTERS ===\n";
foreach (WorkCenter::all() as $wc) {
    echo "WC ID: {$wc->id} | Code: {$wc->code} | Name: {$wc->name} | Line ID: {$wc->production_line_id}\n";
}

echo "\n=== FIRST 20 MACHINES ===\n";
foreach (Machine::with('workCenter.productionLine')->take(20)->get() as $m) {
    $lineId = $m->workCenter ? $m->workCenter->production_line_id : 'NO WC';
    $lineName = $m->workCenter && $m->workCenter->productionLine ? $m->workCenter->productionLine->name : 'NO LINE';
    echo "M ID: {$m->id} | Code: {$m->code} | Name: {$m->name} | WC ID: {$m->work_center_id} | Line: {$lineName} (ID: {$lineId})\n";
}
