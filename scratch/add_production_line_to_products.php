<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Product;
use App\Models\ProductionLine;

if (!Schema::hasColumn('products', 'production_line_id')) {
    Schema::table('products', function (Blueprint $table) {
        $table->foreignId('production_line_id')->nullable()->after('product_category_id');
    });
    echo "Added production_line_id column to products table.\n";
}

$lines = ProductionLine::all()->keyBy('name');

// Map products by ID ranges or explicit line name matching
$products = Product::all();
foreach ($products as $p) {
    $lineName = null;
    if (str_contains($p->name, 'FX3') || str_contains($p->sku, 'FX3')) $lineName = 'FX-3';
    elseif (str_contains($p->name, 'FX7') || str_contains($p->sku, 'FX7')) $lineName = 'FX-7';
    elseif (str_contains($p->name, 'FX9') || str_contains($p->sku, 'FX9')) $lineName = 'FX-9';
    elseif (str_contains($p->name, 'FX11') || str_contains($p->sku, 'FX11')) $lineName = 'FX-11';
    elseif ($p->id <= 10) $lineName = 'FX-1';
    elseif ($p->id <= 20) $lineName = 'FX-2';
    elseif ($p->id <= 30) $lineName = 'FX-3';
    elseif ($p->id <= 35) $lineName = 'FX-4';
    elseif ($p->id <= 40) $lineName = 'FX-5';
    elseif ($p->id <= 45) $lineName = 'FX-6';
    elseif ($p->id <= 49) $lineName = 'FX-7';
    elseif ($p->id <= 54) $lineName = 'FX-8';
    elseif ($p->id <= 59) $lineName = 'FX-9';
    else $lineName = 'FX-11';

    if (isset($lines[$lineName])) {
        $p->production_line_id = $lines[$lineName]->id;
        $p->save();
        echo "Product #{$p->id} ({$p->name}) -> Line {$lineName} (ID {$lines[$lineName]->id})\n";
    }
}

echo "FINISHED mapping products to production lines!\n";
