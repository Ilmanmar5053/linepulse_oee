<?php
require __DIR__ . '/../vendor/autoload.php';
use Carbon\Carbon;

function calcShift($startStr, $endStr, $breakMins = 60) {
    $start = Carbon::parse($startStr);
    $end = Carbon::parse($endStr);
    if ($end->lessThanOrEqualTo($start)) {
        $end->addDay();
    }
    $totalMinutes = (int) $start->diffInMinutes($end);
    $plannedMinutes = max(0, $totalMinutes - $breakMins);
    $totalHours = round($totalMinutes / 60, 2);
    $plannedHours = round($plannedMinutes / 60, 2);

    return [
        'start' => $startStr,
        'end' => $endStr,
        'break_minutes' => $breakMins,
        'total_minutes' => $totalMinutes,
        'total_hours' => $totalHours,
        'planned_production_minutes' => $plannedMinutes,
        'planned_production_hours' => $plannedHours,
    ];
}

print_r(calcShift('07:30', '16:30', 60)); // Shift 1
print_r(calcShift('16:10', '00:10', 60)); // Shift 2
print_r(calcShift('23:50', '07:50', 60)); // Shift 3
