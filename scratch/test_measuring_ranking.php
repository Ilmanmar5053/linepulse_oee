<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;

$ctrl = app(DashboardController::class);
$req = Request::create('/api/v1/dashboard/machine-ranking', 'GET', ['period' => '30days']);
$res = $ctrl->machineRanking($req);
$data = json_decode($res->getContent(), true)['data'];

echo "Total machines in ranking: " . count($data) . "\n";
$measuring = array_filter($data, fn($m) => str_contains($m['code'], 'MEASUR'));
echo "Measuring in ranking:\n";
print_r(array_values($measuring));

echo "\nTop 5 machines in ranking:\n";
print_r(array_slice($data, 0, 5));
