<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;

$ctrl = app(ReportController::class);

// 1. Test All Shifts
$req1 = Request::create('/api/v1/reports/shift-summary', 'GET', []);
$res1 = $ctrl->shiftSummary($req1);
$data1 = json_decode($res1->getContent(), true)['data'];
echo "=== Test 1: All Shifts ===\n";
echo "Shift Name: " . $data1['header']['shift_name'] . "\n";
echo "Shift Window: " . $data1['header']['shift_time_window'] . "\n";
echo "Supervisor: " . $data1['header']['supervisor_in_charge'] . "\n";

// 2. Test Shift 1
$req2 = Request::create('/api/v1/reports/shift-summary', 'GET', ['shift_id' => 1]);
$res2 = $ctrl->shiftSummary($req2);
$data2 = json_decode($res2->getContent(), true)['data'];
echo "\n=== Test 2: Shift 1 ===\n";
echo "Shift Name: " . $data2['header']['shift_name'] . "\n";
echo "Shift Window: " . $data2['header']['shift_time_window'] . "\n";
echo "Supervisor: " . $data2['header']['supervisor_in_charge'] . "\n";

// 3. Test Custom Supervisor
$req3 = Request::create('/api/v1/reports/shift-summary', 'GET', ['supervisor' => 'Ir. Hendra Kusuma']);
$res3 = $ctrl->shiftSummary($req3);
$data3 = json_decode($res3->getContent(), true)['data'];
echo "\n=== Test 3: Custom Supervisor ===\n";
echo "Supervisor: " . $data3['header']['supervisor_in_charge'] . "\n";
echo "Available Supervisors count: " . count($data3['header']['available_supervisors']) . "\n";
print_r($data3['header']['available_supervisors']);
