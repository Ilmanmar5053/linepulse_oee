<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;

$ctrl = $app->make(DashboardController::class);
$req = new Request(['period' => '30d']);

$dt = $ctrl->paretoDowntime($req)->getData(true);
$def = $ctrl->paretoDefects($req)->getData(true);
$mRank = $ctrl->machineRanking($req)->getData(true);
$lRank = $ctrl->lineRanking($req)->getData(true);

echo "Downtime Pareto count: " . count($dt['data'] ?? []) . "\n";
echo "Defect Pareto count: " . count($def['data']['top_10_defects'] ?? []) . "\n";
echo "Machine Ranking count: " . count($mRank['data'] ?? []) . "\n";
echo "Line Ranking count: " . count($lRank['data'] ?? []) . "\n";

echo "\nSample Machine Ranking:\n";
print_r(array_slice($mRank['data'] ?? [], 0, 2));

echo "\nSample Line Ranking:\n";
print_r(array_slice($lRank['data'] ?? [], 0, 2));
