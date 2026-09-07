<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionLine;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

$lines = ProductionLine::with(['area', 'products'])->get();

foreach ($lines as $line) {
    $producedProducts = DB::table('production_records')
        ->join('products', 'production_records.product_id', '=', 'products.id')
        ->where('production_records.production_line_id', $line->id)
        ->select('products.name', 'products.sku')
        ->distinct()
        ->get();
        
    $assignedProducts = $line->products;
    
    $producedNames = $producedProducts->pluck('name')->filter()->values()->all();
    $assignedNames = $assignedProducts->pluck('name')->filter()->values()->all();
    
    $displayList = !empty($producedNames) ? $producedNames : $assignedNames;
    
    echo "Line: {$line->name} (Code: {$line->code})\n";
    echo "  Produced: " . implode(', ', $producedNames) . "\n";
    echo "  Assigned: " . implode(', ', $assignedNames) . "\n";
    echo "  Display List: " . implode(', ', $displayList) . "\n\n";
}
