<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;

$ctrl = app(ReportController::class);
$req = Request::create('/api/v1/reports/export', 'GET', [
    'date' => '2026-08-28',
    'shift_id' => 1,
    'type' => 'excel',
]);

$res = $ctrl->export($req);

echo "Status Code: " . $res->getStatusCode() . "\n";
echo "Content-Type: " . $res->headers->get('Content-Type') . "\n";
echo "Content Length: " . strlen($res->getContent()) . " bytes\n";
echo "Preview first 500 chars:\n" . substr($res->getContent(), 0, 500) . "\n";
file_put_contents(__DIR__ . '/test_output.xls', $res->getContent());
echo "Saved to scratch/test_output.xls\n";
