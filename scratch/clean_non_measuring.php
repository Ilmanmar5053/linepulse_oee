<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionRecord;
use App\Models\OeeRecord;
use App\Models\Machine;

$measuringMc = Machine::where('code', 'MC-MEASURING')->orWhere('name', 'like', '%Measuring%')->first();

if (!$measuringMc) {
    echo "Measuring machine not found!\n";
    exit;
}

echo "Measuring Machine ID: {$measuringMc->id} ({$measuringMc->code} - {$measuringMc->name})\n\n";

$nonMeasuringRecords = ProductionRecord::where('machine_id', '!=', $measuringMc->id)->get();
echo "Found " . $nonMeasuringRecords->count() . " non-measuring production records to delete.\n";

foreach ($nonMeasuringRecords as $rec) {
    OeeRecord::where('production_record_id', $rec->id)->delete();
    echo "Deleted OeeRecord for ProductionRecord ID: {$rec->id} (Machine ID: {$rec->machine_id})\n";
    $rec->delete();
}

echo "\nRemaining Production Records in Database:\n";
$remaining = ProductionRecord::with(['machine', 'productionLine', 'product'])->get();
foreach ($remaining as $r) {
    echo "ID: {$r->id} | Machine: {$r->machine->code} ({$r->machine->name}) | Line: " . ($r->productionLine->name ?? 'N/A') . " | Product: " . ($r->product->name ?? 'N/A') . " | Good: {$r->good_quantity}/{$r->target_quantity}\n";
}
