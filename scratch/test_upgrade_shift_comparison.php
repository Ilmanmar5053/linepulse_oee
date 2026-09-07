<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OeeRecord;
use App\Models\Shift;
use App\Services\Oee\OeeCalculationService;
use Carbon\Carbon;

$oeeService = new OeeCalculationService();
$allShifts = Shift::all();

$records = OeeRecord::with('shift')->get();

$shiftData = [];
$maxOee = -1;
$bestShiftId = null;

foreach ($allShifts as $s) {
    $sRecords = $records->where('shift_id', $s->id);
    $count = $sRecords->count();

    if ($count > 0) {
        $avail = round((float) $sRecords->avg('availability'), 2);
        $perf = round((float) $sRecords->avg('performance'), 2);
        $qual = round((float) $sRecords->avg('quality'), 2);
        $oee = round(($avail / 100) * ($perf / 100) * ($qual / 100) * 100, 2);

        $target = (int) $sRecords->sum('target_quantity');
        $totalActual = (int) $sRecords->sum('total_quantity');
        $good = (int) $sRecords->sum('good_quantity');
        $reject = (int) $sRecords->sum('reject_quantity');

        $plannedMins = (float) $sRecords->sum('planned_production_time_minutes');
        $runMins = (float) $sRecords->sum('run_time_minutes');
        $downMins = (float) $sRecords->sum('downtime_minutes');

        $yieldRate = $totalActual > 0 ? round(($good / $totalActual) * 100, 1) : 100.0;
        $rejectRate = $totalActual > 0 ? round(($reject / $totalActual) * 100, 1) : 0.0;

        if ($oee > $maxOee) {
            $maxOee = $oee;
            $bestShiftId = $s->id;
        }
    } else {
        $avail = 0; $perf = 0; $qual = 0; $oee = 0;
        $target = 0; $totalActual = 0; $good = 0; $reject = 0;
        $plannedMins = 480; $runMins = 0; $downMins = 0;
        $yieldRate = 0; $rejectRate = 0;
    }

    $startStr = substr($s->start_time ?? '07:30', 0, 5);
    $endStr = substr($s->end_time ?? '16:30', 0, 5);

    $shiftData[] = [
        'shift_id' => $s->id,
        'shift_name' => $s->name,
        'working_hours' => "{$startStr} - {$endStr}",
        'count' => $count,
        'availability' => $avail,
        'performance' => $perf,
        'quality' => $qual,
        'oee' => $oee,
        'oee_status' => $oeeService->evaluateStatus($oee),
        'target_quantity' => $target,
        'total_quantity' => $totalActual,
        'good_quantity' => $good,
        'reject_quantity' => $reject,
        'planned_time_minutes' => $plannedMins,
        'run_time_minutes' => $runMins,
        'downtime_minutes' => $downMins,
        'yield_rate' => $yieldRate,
        'rejection_rate' => $rejectRate,
        'is_best_performer' => false,
    ];
}

// Set best performer flag
foreach ($shiftData as &$item) {
    if ($bestShiftId !== null && $item['shift_id'] == $bestShiftId && $item['count'] > 0) {
        $item['is_best_performer'] = true;
    }
}

// Daily breakdown
$dailyGrouped = $records->groupBy('record_date');
$dailyBreakdown = [];

foreach ($dailyGrouped as $dateStr => $dRecords) {
    $dateShifts = [];
    foreach ($allShifts as $s) {
        $dsRecords = $dRecords->where('shift_id', $s->id);
        if ($dsRecords->count() > 0) {
            $da = round((float) $dsRecords->avg('availability'), 2);
            $dp = round((float) $dsRecords->avg('performance'), 2);
            $dq = round((float) $dsRecords->avg('quality'), 2);
            $doee = round(($da / 100) * ($dp / 100) * ($dq / 100) * 100, 2);

            $dateShifts[] = [
                'shift_id' => $s->id,
                'shift_name' => $s->name,
                'oee' => $doee,
                'total_quantity' => (int) $dsRecords->sum('total_quantity'),
                'good_quantity' => (int) $dsRecords->sum('good_quantity'),
                'reject_quantity' => (int) $dsRecords->sum('reject_quantity'),
            ];
        }
    }
    $dailyBreakdown[] = [
        'date' => Carbon::parse($dateStr)->format('d M Y'),
        'raw_date' => $dateStr,
        'shifts' => $dateShifts
    ];
}

echo json_encode([
    'shifts' => $shiftData,
    'daily_breakdown' => $dailyBreakdown
], JSON_PRETTY_PRINT);
