<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\MasterDataController;
use Illuminate\Http\Request;
use App\Models\Machine;

$mc = Machine::where('code', 'MC-MEASURING')->first();
echo "Testing update for Machine ID: {$mc->id} ({$mc->code})...\n";

$ctrl = app(MasterDataController::class);
$req = Request::create("/api/v1/master/machines/{$mc->id}", 'PUT', [
    'code' => 'MC-MEASURING',
    'name' => 'Measuring Machine High Precision',
    'work_center_id' => 17,
    'status' => 'RUNNING',
]);

$res = $ctrl->updateMachine($req, $mc->id);
echo "Response status: " . $res->getStatusCode() . "\n";
echo "Response content: " . $res->getContent() . "\n";

$fresh = Machine::find($mc->id);
echo "\nUpdated Machine: {$fresh->code} - {$fresh->name} (Status: {$fresh->status->value})\n";
