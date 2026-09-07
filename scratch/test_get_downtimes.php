<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\DowntimeController;
use Illuminate\Http\Request;

$controller = app(DowntimeController::class);
$req = new Request();
$res = $controller->index($req);

echo "=== API DOWNTIMES INDEX RESPONSE ===\n";
print_r(json_decode($res->getContent(), true));
