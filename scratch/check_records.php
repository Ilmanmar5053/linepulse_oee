<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionRecord;
use App\Models\Machine;

$records = ProductionRecord::with(['machine', 'productionLine', 'product', 'oeeRecord'])->get();
echo "Total Production Records in DB: " . $records->count() . "\n\n";

foreach ($records as $r) {
    echo "ID: {$r->id} | Machine: {$r->machine->code} | Line: " . ($r->productionLine->name ?? 'N/A') . " | Product: " . ($r->product->name ?? 'N/A') . " | Good: {$r->good_quantity}/{$r->target_quantity} | Date: {$r->production_date}\n";
}
