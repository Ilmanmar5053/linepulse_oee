<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductionLine;

$lineFx10 = ProductionLine::where('code', 'FX-10')->first();
echo "Line FX-10 ID: " . $lineFx10->id . "\n";

$testSku = "TEST-FX10-" . time();
$newProduct = Product::create([
    'production_line_id' => $lineFx10->id,
    'sku' => $testSku,
    'name' => 'Test Product FX-10 Auto',
    'ideal_cycle_time' => 6.2,
    'unit_of_measure' => 'PCS',
]);

echo "Created Product ID: {$newProduct->id}\n";
echo "Loaded Line: " . ($newProduct->productionLine ? $newProduct->productionLine->name : 'None') . "\n";

// Clean up test product
$newProduct->forceDelete();
echo "Cleaned up test product.\n";
