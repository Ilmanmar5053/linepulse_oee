<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$lines = App\Models\ProductionLine::all();
$products = App\Models\Product::all();

foreach ($lines as $line) {
    echo "========================================\n";
    echo "LINE: {$line->code} (ID: {$line->id})\n";
    echo "========================================\n";
    $filtered = $products->filter(function($p) use ($line) {
        return $p->production_line_id == $line->id;
    });

    foreach ($filtered as $p) {
        echo "  - {$p->name} | SKU: {$p->sku} | Cycle: {$p->ideal_cycle_time}s\n";
    }
    if ($filtered->isEmpty()) {
        echo "  [WARNING: NO PRODUCTS FOUND FOR THIS LINE!]\n";
    }
}
