<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;

$m = Machine::where('code', 'MC-MEASURING')->first();
echo "Found MC-MEASURING in active machines list: \n";
echo "ID: " . $m->id . "\n";
echo "Code: " . $m->code . "\n";
echo "Name: " . $m->name . "\n";
echo "Work Center ID: " . $m->work_center_id . "\n";
echo "Active: " . $m->is_active . "\n";
echo "Deleted at: " . ($m->deleted_at ?? 'NULL (Active)') . "\n";
