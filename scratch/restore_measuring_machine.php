<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;
use App\Models\WorkCenter;
use Illuminate\Support\Facades\DB;

$wc = WorkCenter::all();
echo "Work Centers:\n";
foreach ($wc as $w) {
    echo "ID: {$w->id}, Name: {$w->name}, Line: {$w->line_id}\n";
}

DB::table('machines')->where('id', 411)->update([
    'deleted_at' => null,
    'is_active' => 1,
    'status' => 'RUNNING',
    'updated_at' => now(),
]);

$mc = Machine::find(411);
echo "\nMachine MC-MEASURING restored successfully!\n";
print_r($mc->toArray());
