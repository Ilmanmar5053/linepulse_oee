<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;

$controller = app(DashboardController::class);
$req = new Request();
$res = $controller->paretoDefects($req);
$data = $res->getData();

echo "=== API RESULT TEST: paretoDefects ===\n";
echo "Success: " . ($data->success ? 'YES' : 'NO') . "\n";
echo "Total Defects returned: " . count($data->data) . "\n";
echo "Top 1 Defect: " . ($data->summary->top_defect_name ?? 'N/A') . " (" . ($data->summary->top_defect_qty ?? 0) . " pcs, " . ($data->summary->top_defect_pct ?? 0) . "%)\n";
echo "Top 1 Section: " . ($data->summary->top_section_name ?? 'N/A') . "\n";
echo "Total Rejects: " . ($data->summary->total_reject_pcs ?? 0) . " pcs\n";
echo "PPM: " . ($data->summary->ppm_defect_rate ?? 0) . " PPM\n";
echo "Vital Few Count: " . ($data->summary->vital_few_count ?? 0) . " (" . ($data->summary->vital_few_percentage ?? 0) . "% of defects)\n";

echo "\n--- First 3 Top Defects in data ---\n";
foreach (array_slice($data->data, 0, 3) as $d) {
    echo "#{$d->rank} | {$d->reason} | {$d->reject_quantity} pcs ({$d->percentage}%) | Cum: {$d->cumulative_percentage}% | Vital: " . ($d->is_vital_few ? 'YES' : 'NO') . "\n";
    echo "   Section: {$d->section} | Comp: {$d->component_type}\n";
    echo "   Countermeasure: {$d->action_recommendation}\n";
}
