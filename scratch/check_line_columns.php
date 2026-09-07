<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use App\Models\ProductionLine;

echo "=== PRODUCTION LINES TABLE COLUMNS ===\n";
$cols = Schema::getColumnListing('production_lines');
print_r($cols);

echo "\n=== PRODUCTION LINES DATA WITH AREA & PLANT ===\n";
$lines = ProductionLine::with('area.plant')->get();
foreach ($lines as $l) {
    echo "Line ID: {$l->id} | Code: {$l->code} | Name: {$l->name} | Area ID: {$l->area_id} | Area Name: " . ($l->area->name ?? 'NULL') . " | Plant ID: " . ($l->area->plant->id ?? 'NULL') . " | Plant Name: " . ($l->area->plant->name ?? 'N/A') . "\n";
}
