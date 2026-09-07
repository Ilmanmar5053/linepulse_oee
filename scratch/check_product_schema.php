<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\ProductionLine;

echo "Products Table Columns:\n";
print_r(Schema::getColumnListing('products'));

echo "\nSample Product record:\n";
$p = Product::first();
if ($p) {
    print_r($p->toArray());
}

echo "\nAll Production Lines:\n";
$lines = ProductionLine::all(['id', 'code', 'name']);
print_r($lines->toArray());
