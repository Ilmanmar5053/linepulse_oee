<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductionLine;

$products = Product::all();
echo "Total Products: " . $products->count() . "\n";

foreach ($products as $p) {
    echo "ID: {$p->id} | SKU: {$p->sku} | Name: {$p->name} | CycleTime: {$p->ideal_cycle_time}\n";
}
