<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Http\Controllers\Api\DashboardController;

$controller = new DashboardController(new App\Services\Oee\OeeCalculationService());
$request = Request::create('/api/dashboard/shift-comparison', 'GET');
$response = $controller->shiftComparison($request);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Response Body:\n";
echo $response->getContent() . "\n";
