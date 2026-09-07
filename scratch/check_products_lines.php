<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lines = App\Models\ProductionLine::all();
echo "=== PRODUCTION LINES ===\n";
foreach ($lines as $line) {
    echo "Line ID: {$line->id} | Code: {$line->code} | Name: {$line->name}\n";
}

$products = App\Models\Product::all();
echo "\n=== PRODUCTS ===\n";
foreach ($products as $p) {
    echo "ID: {$p->id} | LineID: {$p->production_line_id} | Name: {$p->name} | SKU: {$p->sku}\n";
}
