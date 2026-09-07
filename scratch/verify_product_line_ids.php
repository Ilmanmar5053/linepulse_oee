<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\ProductionLine;

$lines = ProductionLine::all();
echo "Production Lines:\n";
foreach ($lines as $l) {
    $count = Product::where('production_line_id', $l->id)->count();
    echo "ID: {$l->id} | Name: {$l->name} | Products Count: {$count}\n";
}

echo "\nFX-1 Products:\n";
$fx1Line = ProductionLine::where('name', 'FX-1')->first();
if ($fx1Line) {
    $fx1Prods = Product::where('production_line_id', $fx1Line->id)->get();
    foreach ($fx1Prods as $p) {
        echo "ID: {$p->id} | Name: {$p->name} | Line ID: {$p->production_line_id}\n";
    }
}
