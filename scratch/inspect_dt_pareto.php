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
echo "getParetoDowntime output structure:\n";
print_r($dt);
