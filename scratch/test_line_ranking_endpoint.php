<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;

$ctrl = $app->make(DashboardController::class);
$req = new Request();

try {
    $res = $ctrl->lineRanking($req);
    $data = $res->getData(true);
    echo "lineRanking SUCCESS (HTTP 200):\n";
    foreach ($data['data'] as $line) {
        echo "Line: {$line['name']} | Products: {$line['product_summary']}\n";
    }
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
