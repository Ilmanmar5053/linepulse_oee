<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;
use App\Models\ProductionLine;

echo "=== CHECKING MACHINES ===\n";
$machines = Machine::withTrashed()->get();
foreach ($machines as $m) {
    echo "ID: {$m->id} | Code: {$m->code} | Name: {$m->name} | Line ID: {$m->production_line_id} | Deleted: " . ($m->deleted_at ?: 'NO') . "\n";
}

echo "\n=== CHECKING PRODUCTION LINES ===\n";
$lines = ProductionLine::all();
foreach ($lines as $l) {
    echo "ID: {$l->id} | Code: {$l->code} | Name: {$l->name}\n";
}
