<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionLine;
use App\Models\Plant;
use App\Models\Area;

echo "=== PLANTS ===\n";
$plants = Plant::all();
foreach ($plants as $p) {
    echo "Plant ID: {$p->id} | Code: {$p->code} | Name: {$p->name}\n";
}

echo "\n=== AREAS ===\n";
$areas = Area::with('plant')->get();
foreach ($areas as $a) {
    echo "Area ID: {$a->id} | Plant ID: {$a->plant_id} | Name: {$a->name} | Plant: " . ($a->plant->name ?? 'N/A') . "\n";
}

echo "\n=== PRODUCTION LINES SAMPLE ===\n";
$lines = ProductionLine::with('area.plant', 'plant')->get();
foreach ($lines as $l) {
    $plantName = $l->plant->name ?? $l->area->plant->name ?? 'N/A';
    echo "Line ID: {$l->id} | Code: {$l->code} | Name: {$l->name} | Area ID: {$l->area_id} | Plant: {$plantName}\n";
}
