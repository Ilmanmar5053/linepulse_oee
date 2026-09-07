<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\NgRecord;
use App\Models\NgRecordItem;
use App\Models\QualityRecord;
use App\Models\ProductionRecord;

echo "=== COUNT CHECK ===\n";
echo "NgRecord count: " . NgRecord::count() . "\n";
echo "NgRecordItem count: " . NgRecordItem::count() . "\n";
echo "QualityRecord count: " . QualityRecord::count() . "\n";
echo "ProductionRecord with reject/scrap > 0 count: " . ProductionRecord::where('reject_quantity', '>', 0)->orWhere('scrap_quantity', '>', 0)->count() . "\n";

echo "\n=== NG RECORD ITEMS SAMPLES ===\n";
foreach (NgRecordItem::with('ngRecord')->take(10)->get() as $item) {
    echo "ID: {$item->id} | Type: {$item->component_type} | Qty: {$item->quantity} | Section: {$item->section} | Reason: {$item->reason}\n";
}

echo "\n=== NG RECORDS SAMPLES ===\n";
foreach (NgRecord::take(5)->get() as $ng) {
    echo "NG ID: {$ng->id} | Date: {$ng->production_date} | Assy: {$ng->ng_assy} ({$ng->assy_section} / {$ng->assy_reason}) | Rod: {$ng->ng_rod} | Cap: {$ng->ng_cap}\n";
}
