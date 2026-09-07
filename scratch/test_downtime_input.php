<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionRecord;
use App\Models\Downtime;
use App\Models\ProductionLine;
use App\Models\Machine;
use App\Models\Product;
use App\Models\Shift;

echo "=== CREATING SAMPLE DOWNTIME LOG FOR TESTING ===\n";

$line = ProductionLine::first();
$machine = Machine::first();
$product = Product::first();
$shift = Shift::first();

if ($line && $machine && $product && $shift) {
    $dt = Downtime::create([
        'production_line_id' => $line->id,
        'machine_id' => $machine->id,
        'product_id' => $product->id,
        'shift_id' => $shift->id,
        'team' => 'Team A (Regu 1)',
        'problem_type' => 'Mesin',
        'start_time' => '2026-08-19 09:00:00',
        'end_time' => '2026-08-19 09:45:00',
        'duration_minutes' => 45,
        'downtime_reason_id' => 3, // Mechanical Jam
        'description' => 'Motor spindle overheat dan sensor misfeed pada stasiun 2',
        'action_taken' => 'Pembersihan sensor optic, penggantian v-belt dan reset alarm PLC',
        'is_planned' => false,
    ]);

    echo "Sample Downtime Log Created with ID: {$dt->id}\n";
    echo "Line: {$line->name} | Shift: {$shift->name} | Team: {$dt->team} | Type: {$dt->problem_type} | Duration: {$dt->duration_minutes} mins\n";
    echo "Description: {$dt->description}\n";
    echo "Action Taken: {$dt->action_taken}\n";
} else {
    echo "Master data missing to create sample log.\n";
}
