<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;
use App\Models\ProductionLine;

$machines = Machine::all();
echo "Total Machines: " . $machines->count() . "\n";
foreach ($machines->take(30) as $m) {
    echo "ID: {$m->id} | Code: {$m->code} | Name: {$m->name} | Line ID: " . var_export($m->production_line_id, true) . "\n";
}

$lines = ProductionLine::all();
echo "\nTotal Lines: " . $lines->count() . "\n";
foreach ($lines as $l) {
    echo "ID: {$l->id} | Code: {$l->code} | Name: {$l->name}\n";
}
