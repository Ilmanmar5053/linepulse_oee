<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Machine;
use Illuminate\Support\Facades\DB;

DB::table('machines')->where('id', 411)->update([
    'work_center_id' => 17,
    'deleted_at' => null,
    'is_active' => 1,
    'status' => 'RUNNING',
    'updated_at' => now(),
]);

$mc = Machine::with('workCenter.productionLine')->find(411);
echo "Restored Machine Details:\n";
echo "ID: {$mc->id}\n";
echo "Code: {$mc->code}\n";
echo "Name: {$mc->name}\n";
echo "Status: {$mc->status}\n";
echo "Active: {$mc->is_active}\n";
echo "WorkCenter ID: {$mc->work_center_id}\n";
echo "Line Code: " . ($mc->workCenter->productionLine->code ?? 'N/A') . "\n";
echo "Line Name: " . ($mc->workCenter->productionLine->name ?? 'N/A') . "\n";
