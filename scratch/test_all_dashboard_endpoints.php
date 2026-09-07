<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;

$ctrl = $app->make(DashboardController::class);
$req = new Request();

$endpoints = [
    'overview' => fn() => $ctrl->overview($req),
    'oeeTrend' => fn() => $ctrl->oeeTrend($req),
    'sixBigLosses' => fn() => $ctrl->sixBigLosses($req),
    'paretoDowntime' => fn() => $ctrl->paretoDowntime($req),
    'paretoDefects' => fn() => $ctrl->paretoDefects($req),
    'machineRanking' => fn() => $ctrl->machineRanking($req),
    'lineRanking' => fn() => $ctrl->lineRanking($req),
    'shiftComparison' => fn() => $ctrl->shiftComparison($req),
];

echo "Testing all DashboardController endpoints:\n";
foreach ($endpoints as $name => $fn) {
    try {
        $res = $fn();
        echo "  [OK 200] {$name}\n";
    } catch (\Throwable $e) {
        echo "  [FAIL] {$name}: " . $e->getMessage() . "\n";
    }
}
