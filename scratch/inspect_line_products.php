<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionLine;
use App\Models\Product;
use App\Models\ProductionRecord;
use Illuminate\Support\Facades\DB;

$lines = ProductionLine::all();
echo "Production Lines (" . count($lines) . "):\n";
foreach ($lines as $l) {
    // 1. Products assigned to line
    $assignedProducts = Product::where('production_line_id', $l->id)->get();
    
    // 2. Products actually produced in production_records
    $producedProducts = DB::table('production_records')
        ->join('products', 'production_records.product_id', '=', 'products.id')
        ->where('production_records.production_line_id', $l->id)
        ->select('products.name', 'products.sku')
        ->distinct()
        ->get();
        
    echo "Line ID: {$l->id} | Code: {$l->code} | Name: {$l->name}\n";
    echo "  Assigned in Master: " . $assignedProducts->pluck('name')->implode(', ') . "\n";
    echo "  Produced in Records: " . $producedProducts->pluck('name')->implode(', ') . "\n";
}
