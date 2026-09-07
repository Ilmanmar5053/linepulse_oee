<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\ProductionLine;
use App\Models\Plant;
use App\Models\Area;

if (!Schema::hasColumn('production_lines', 'plant_id')) {
    Schema::table('production_lines', function (Blueprint $table) {
        $table->foreignId('plant_id')->nullable()->after('area_id')->constrained('plants')->nullOnDelete();
    });
    echo "Added plant_id column to production_lines table.\n";
} else {
    echo "Column plant_id already exists in production_lines.\n";
}

// Backfill plant_id from area.plant_id
$lines = ProductionLine::with('area')->get();
$defaultPlantId = Plant::first()->id ?? 1;

foreach ($lines as $l) {
    $plantId = $l->area->plant_id ?? $defaultPlantId;
    $l->plant_id = $plantId;
    $l->save();
}

echo "Backfilled plant_id for all production lines successfully.\n";
