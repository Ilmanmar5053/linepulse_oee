<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;

$ctrl = app(ReportController::class);
$req = Request::create('/api/v1/reports/shift-summary', 'GET', [
    'date' => '2026-08-28',
    'shift_id' => 1,
]);

$res = $ctrl->shiftSummary($req);
$data = json_decode($res->getContent(), true);

echo "=== REPORT INCIDENT LOGS (CAPA & RCA) ===\n";
print_r($data['data']['incident_logs']);

echo "\n=== SHIFT HANDOVER NOTES ===\n";
print_r($data['data']['handover_notes']);
