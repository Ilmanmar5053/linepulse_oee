<?php

namespace App\Http\Controllers\Api;

use App\Enums\SixBigLoss;
use App\Http\Controllers\Controller;
use App\Models\Downtime;
use App\Models\Machine;
use App\Models\OeeRecord;
use App\Models\Plant;
use App\Models\ProductionLine;
use App\Models\ProductionRecord;
use App\Models\QualityRecord;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Get Comprehensive Shift Production & OEE Handover Summary
     */
    public function shiftSummary(Request $request): JsonResponse
    {
        $startDateStr = $request->get('start_date') ?: $request->get('date_from');
        $endDateStr = $request->get('end_date') ?: $request->get('date_to');
        $dateStr = $request->get('date');
        $shiftId = $request->get('shift_id');
        $lineId = $request->get('line_id');
        $period = $request->get('period');

        // Resolve Date Filter
        if ($startDateStr && $endDateStr) {
            $startDate = Carbon::parse($startDateStr)->startOfDay();
            $endDate = Carbon::parse($endDateStr)->endOfDay();
        } elseif ($startDateStr) {
            $startDate = Carbon::parse($startDateStr)->startOfDay();
            $endDate = Carbon::parse($startDateStr)->endOfDay();
        } elseif ($dateStr) {
            $startDate = Carbon::parse($dateStr)->startOfDay();
            $endDate = Carbon::parse($dateStr)->endOfDay();
        } elseif ($period === 'yesterday') {
            $startDate = Carbon::yesterday()->startOfDay();
            $endDate = Carbon::yesterday()->endOfDay();
        } elseif ($period === '7days') {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($period === '30days') {
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } else {
            // Default: Most recent date in production records or today
            $latestRecordDate = ProductionRecord::max('production_date');
            $startDate = $latestRecordDate ? Carbon::parse($latestRecordDate)->startOfDay() : Carbon::today()->startOfDay();
            $endDate = $latestRecordDate ? Carbon::parse($latestRecordDate)->endOfDay() : Carbon::today()->endOfDay();
        }

        // 1. Query Production Records with Full Relations
        $prodQuery = ProductionRecord::with([
            'productionLine.plant',
            'machine',
            'product',
            'shift',
        ])->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()]);

        if ($shiftId) {
            $prodQuery->where('shift_id', $shiftId);
        }
        if ($lineId) {
            $prodQuery->where('production_line_id', $lineId);
        }
        if ($request->filled('product_id')) {
            $prodQuery->where('product_id', $request->product_id);
        }

        $prodRecords = $prodQuery->orderBy('production_date', 'asc')
            ->orderBy('production_line_id', 'asc')
            ->orderBy('machine_id', 'asc')
            ->get();

        // 2. Query OEE Records
        $oeeQuery = OeeRecord::with(['productionLine', 'machine', 'shift'])
            ->whereBetween('record_date', [$startDate->toDateString(), $endDate->toDateString()]);
        if ($shiftId) {
            $oeeQuery->where('shift_id', $shiftId);
        }
        if ($lineId) {
            $oeeQuery->where('production_line_id', $lineId);
        }
        $oeeRecords = $oeeQuery->get()->keyBy(function ($item) {
            return ($item->production_record_id ?: "{$item->machine_id}_{$item->shift_id}_{$item->record_date}");
        });

        // 3. Query Downtimes for the period
        $downQuery = Downtime::with(['productionLine', 'machine', 'downtimeReason'])
            ->whereBetween('start_time', [$startDate, $endDate]);
        if ($lineId) {
            $downQuery->where('production_line_id', $lineId);
        }
        $downtimes = $downQuery->orderBy('start_time', 'desc')->get();

        // 4. Resolve Header Metadata
        $plant = Plant::first();
        $selectedShift = $shiftId ? Shift::find($shiftId) : null;
        $selectedLine = $lineId ? ProductionLine::find($lineId) : null;

        // Dynamic Supervisor Lookup
        $requestedSupervisor = $request->get('supervisor');
        if (!empty($requestedSupervisor)) {
            $supervisor = trim($requestedSupervisor);
        } else {
            $group = $selectedLine ? \App\Models\Group::where('production_line_id', $selectedLine->id)->first() : \App\Models\Group::first();
            $supervisor = $group?->supervisor_name ?? $group?->leader_name ?? 'Alim Utama';
        }

        $supervisorsList = \App\Models\Group::whereNotNull('supervisor_name')
            ->pluck('supervisor_name')
            ->merge(\App\Models\Group::pluck('leader_name'))
            ->merge(\App\Models\User::pluck('name'))
            ->unique()
            ->filter()
            ->values();

        $profileRaw = \App\Models\SystemSetting::get('company_profile');
        $profile = is_array($profileRaw) ? $profileRaw : ($profileRaw ? json_decode($profileRaw, true) : []);

        if ($selectedShift) {
            $shiftName = $selectedShift->name;
            $shiftWindow = ($selectedShift->start_time && $selectedShift->end_time) 
                ? Carbon::parse($selectedShift->start_time)->format('H:i') . ' - ' . Carbon::parse($selectedShift->end_time)->format('H:i') . ' WIB'
                : '07:00 - 15:00 WIB';
            $shiftCode = "SHIFT {$selectedShift->id}";
        } else {
            $shiftName = 'All Shifts (Consolidated)';
            $shiftWindow = '24 Hours Operational Cycle (All Shifts)';
            $shiftCode = 'ALL SHIFTS';
        }

        $dateRangeLabel = ($startDate->toDateString() === $endDate->toDateString())
            ? $startDate->format('d F Y')
            : ($startDate->format('d M Y') . ' s/d ' . $endDate->format('d M Y'));

        $header = [
            'plant_id' => $profile['plant_code'] ?? ($plant ? "PLT-0{$plant->id}" : 'PLT-01'),
            'plant_name' => $profile['plant_name'] ?? ($plant?->name ?? 'ENGINE PARTS & AIR PUMP MFG'),
            'plant_location' => $profile['plant_location'] ?? ($plant?->location ?? 'Kawasan Industri Modern Cikande, Serang - Banten'),
            'company_name' => $profile['company_name'] ?? 'PT. YASUNAGA INDONESIA',
            'company_tagline' => $profile['company_tagline'] ?? 'Engine Parts & Air Pump Manufacturing',
            'company_logo' => !empty($profile['company_logo']) ? $profile['company_logo'] : '/images/yasunaga-logo.png',
            'address' => $profile['address'] ?? 'Jl. Modern Industri Raya Kav. 24 Kawasan Industri Modern Cikande, Nambo Ilir Kibin Serang Banten',
            'phone' => $profile['phone'] ?? '(0254) 400306',
            'email' => $profile['email'] ?? 'prodcr@yasunaga.co.id',
            'website' => $profile['website'] ?? 'www.yasunaga.co.jp',
            'doc_prefix' => $profile['doc_prefix'] ?? 'YSN-OEE',
            'line_id' => $selectedLine ? "LIN-0{$selectedLine->id}" : 'ALL-LINES',
            'line_name' => $selectedLine ? $selectedLine->name : 'All Production Lines',
            'shift_id' => $selectedShift?->id ?? 'ALL',
            'shift_code' => $shiftCode,
            'shift_name' => $shiftName,
            'shift_time_window' => $shiftWindow,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'production_date' => $startDate->toDateString(),
            'formatted_date' => $dateRangeLabel,
            'date_range_label' => $dateRangeLabel,
            'is_date_range' => ($startDate->toDateString() !== $endDate->toDateString()),
            'supervisor_in_charge' => $supervisor,
            'available_supervisors' => $supervisorsList,
            'status' => 'COMPLETED',
            'baseline_target_oee' => 85.00,
            'baseline_target_availability' => 90.00,
            'baseline_target_performance' => 95.00,
            'baseline_target_quality' => 99.00,
        ];

        // 5. Aggregate Macro KPIs
        $totalPlannedMinutes = (float) $prodRecords->sum('planned_production_time');
        $totalDowntimeMinutes = (float) $prodRecords->sum('downtime');
        $totalRunTimeMinutes = (float) $prodRecords->sum('run_time');
        $totalOutput = (int) $prodRecords->sum('total_quantity');
        $totalGood = (int) $prodRecords->sum('good_quantity');
        $totalReject = (int) $prodRecords->sum('reject_quantity');
        $totalScrap = (int) $prodRecords->sum('scrap_quantity');
        $totalDefectAll = $totalReject + $totalScrap;

        $avgAvail = $totalPlannedMinutes > 0 ? round(($totalRunTimeMinutes / $totalPlannedMinutes) * 100, 2) : 0.0;
        $avgPerf = $prodRecords->count() > 0 ? round($prodRecords->avg(function ($r) {
            $ict = (float) $r->ideal_cycle_time;
            $rt = (float) $r->run_time;
            $tq = (int) $r->total_quantity;
            return ($rt > 0 && $ict > 0) ? (($tq * $ict) / ($rt * 60.0)) * 100 : 0;
        }), 2) : 0.0;
        $avgQual = $totalOutput > 0 ? round(($totalGood / $totalOutput) * 100, 2) : 100.0;
        $overallOee = round(($avgAvail * $avgPerf * $avgQual) / 10000.0, 2);

        $executiveSummary = [
            'overall_oee' => $overallOee,
            'availability' => $avgAvail,
            'performance' => $avgPerf,
            'quality' => $avgQual,
            'total_planned_minutes' => $totalPlannedMinutes,
            'total_downtime_minutes' => $totalDowntimeMinutes,
            'total_run_time_minutes' => $totalRunTimeMinutes,
            'total_output_pcs' => $totalOutput,
            'good_output_pcs' => $totalGood,
            'reject_output_pcs' => $totalReject,
            'scrap_output_pcs' => $totalScrap,
            'total_defect_pcs' => $totalDefectAll,
            'yield_rate_pct' => $totalOutput > 0 ? round(($totalGood / $totalOutput) * 100, 2) : 100.0,
            'deviations' => [
                'oee' => round($overallOee - 85.0, 2),
                'availability' => round($avgAvail - 90.0, 2),
                'performance' => round($avgPerf - 95.0, 2),
                'quality' => round($avgQual - 99.0, 2),
            ],
            'oee_status' => $overallOee >= 85 ? 'OPTIMAL' : ($overallOee >= 65 ? 'WARNING' : 'CRITICAL'),
            'oee_badge' => $overallOee >= 85 ? '[● OPTIMAL - WORLD CLASS]' : ($overallOee >= 65 ? '[▲ WARNING - MINOR LOSS]' : '[■ CRITICAL - BREAKDOWN]'),
        ];

        // 6. Build Multi-Line Matrix (Core Reporting Table Rows)
        $matrixRows = [];
        foreach ($prodRecords as $record) {
            $oeeRec = $oeeRecords->get($record->id) ?? $oeeRecords->get("{$record->machine_id}_{$record->shift_id}_{$record->production_date}");
            $rowOee = $oeeRec ? (float) $oeeRec->oee : (float) ($record->oee ?? 0.0);
            $rowAvail = $oeeRec ? (float) $oeeRec->availability : ($record->planned_production_time > 0 ? round(($record->run_time / $record->planned_production_time) * 100, 2) : 0.0);
            $rowPerf = $oeeRec ? (float) $oeeRec->performance : (($record->run_time > 0 && $record->ideal_cycle_time > 0) ? round((($record->total_quantity * $record->ideal_cycle_time) / ($record->run_time * 60.0)) * 100, 2) : 0.0);
            $rowQual = $oeeRec ? (float) $oeeRec->quality : ($record->total_quantity > 0 ? round(($record->good_quantity / $record->total_quantity) * 100, 2) : 100.0);

            $statusBadge = $rowOee >= 85.0 ? '[● OPTIMAL - WORLD CLASS]' : ($rowOee >= 65.0 ? '[▲ WARNING - MINOR LOSS]' : '[■ CRITICAL - BREAKDOWN]');
            $statusClass = $rowOee >= 85.0 ? 'optimal' : ($rowOee >= 65.0 ? 'warning' : 'critical');

            $targetSpeed = $record->ideal_cycle_time > 0 ? round(60.0 / (float) $record->ideal_cycle_time, 1) : 0.0;

            // Matching machine downtimes
            $rowDowntimes = $downtimes->where('machine_id', $record->machine_id)->map(function ($dt) {
                return [
                    'id' => $dt->id,
                    'reason' => $dt->downtimeReason->name ?? 'Unspecified Problem',
                    'duration' => (float) $dt->duration_minutes,
                    'is_planned' => (bool) $dt->is_planned,
                    'start_time' => $dt->start_time ? $dt->start_time->format('H:i') : '-',
                    'end_time' => $dt->end_time ? $dt->end_time->format('H:i') : '-',
                ];
            })->values();

            $matrixRows[] = [
                'record_id' => $record->id,
                'production_date' => $record->production_date ? Carbon::parse($record->production_date)->format('Y-m-d') : '-',
                'formatted_date' => $record->production_date ? Carbon::parse($record->production_date)->format('d M Y') : '-',
                'shift_name' => $record->shift->name ?? "Shift {$record->shift_id}",
                'shift_code' => $record->shift->code ?? "S{$record->shift_id}",
                'line_id' => "LIN-0{$record->production_line_id}",
                'line_name' => $record->productionLine->name ?? "Line #{$record->production_line_id}",
                'machine_id' => $record->machine_id,
                'machine_code' => $record->machine->code ?? "MC-{$record->machine_id}",
                'machine_name' => $record->machine->name ?? 'Standard Machine',
                'product_sku' => $record->product ? "{$record->product->name} - {$record->product->sku}" : ($record->product->sku ?? 'Standard Product - SKU-GEN-001'),
                'product_name' => $record->product->name ?? 'Standard Product',
                'ideal_cycle_time' => (float) $record->ideal_cycle_time,
                'target_speed' => $targetSpeed,
                'planned_time_minutes' => (float) $record->planned_production_time,
                'downtime_minutes' => (float) $record->downtime,
                'run_time_minutes' => (float) $record->run_time,
                'total_output_pcs' => (int) $record->total_quantity,
                'good_output_pcs' => (int) $record->good_quantity,
                'reject_pcs' => (int) $record->reject_quantity,
                'scrap_pcs' => (int) $record->scrap_quantity,
                'reject_output_pcs' => (int) $record->reject_quantity + (int) $record->scrap_quantity,
                'total_defect_pcs' => (int) $record->reject_quantity + (int) $record->scrap_quantity,
                'availability_pct' => $rowAvail,
                'performance_pct' => $rowPerf,
                'quality_pct' => $rowQual,
                'oee_pct' => $rowOee,
                'status_badge' => $statusBadge,
                'status_class' => $statusClass,
                'downtime_logs' => $rowDowntimes,
            ];
        }

        // 7. Aggregate Six Big Losses Breakdown
        $sixLossTotals = [
            'EQUIPMENT_FAILURE' => 0.0,
            'SETUP_ADJUSTMENT' => 0.0,
            'IDLING_MINOR_STOP' => 0.0,
            'REDUCED_SPEED' => 0.0,
            'PROCESS_DEFECTS' => 0.0,
            'REDUCED_YIELD' => 0.0,
        ];

        foreach ($oeeRecords as $oeeRec) {
            $sum = $oeeRec->six_big_losses_summary ?? [];
            foreach ($sixLossTotals as $k => $v) {
                $sixLossTotals[$k] += ($sum[$k] ?? 0.0);
            }
        }

        $totalLossMins = array_sum($sixLossTotals);
        if ($totalLossMins == 0 && $totalDowntimeMinutes > 0) {
            // Fallback estimation from downtime and rejects
            $sixLossTotals['EQUIPMENT_FAILURE'] = round($totalDowntimeMinutes * 0.65, 1);
            $sixLossTotals['SETUP_ADJUSTMENT'] = round($totalDowntimeMinutes * 0.35, 1);
            $sixLossTotals['PROCESS_DEFECTS'] = round(($totalReject * 10) / 60.0, 1);
            $sixLossTotals['REDUCED_YIELD'] = round(($totalScrap * 10) / 60.0, 1);
            $totalLossMins = array_sum($sixLossTotals);
        }

        $sixLossList = [
            [
                'loss' => 'Equipment Failure',
                'category' => 'Availability',
                'minutes' => round($sixLossTotals['EQUIPMENT_FAILURE'], 1),
                'hours' => round($sixLossTotals['EQUIPMENT_FAILURE'] / 60.0, 2),
                'percentage' => $totalLossMins > 0 ? round(($sixLossTotals['EQUIPMENT_FAILURE'] / $totalLossMins) * 100, 1) : 0,
                'description' => 'Unplanned machine mechanical & electrical breakdowns',
            ],
            [
                'loss' => 'Setup & Adjustment',
                'category' => 'Availability',
                'minutes' => round($sixLossTotals['SETUP_ADJUSTMENT'], 1),
                'hours' => round($sixLossTotals['SETUP_ADJUSTMENT'] / 60.0, 2),
                'percentage' => $totalLossMins > 0 ? round(($sixLossTotals['SETUP_ADJUSTMENT'] / $totalLossMins) * 100, 1) : 0,
                'description' => 'Tooling, mold changeover, and warm-up calibrations',
            ],
            [
                'loss' => 'Idling & Minor Stops',
                'category' => 'Performance',
                'minutes' => round($sixLossTotals['IDLING_MINOR_STOP'], 1),
                'hours' => round($sixLossTotals['IDLING_MINOR_STOP'] / 60.0, 2),
                'percentage' => $totalLossMins > 0 ? round(($sixLossTotals['IDLING_MINOR_STOP'] / $totalLossMins) * 100, 1) : 0,
                'description' => 'Brief interruptions, sensor jams, and material feeding stalls',
            ],
            [
                'loss' => 'Reduced Speed',
                'category' => 'Performance',
                'minutes' => round($sixLossTotals['REDUCED_SPEED'], 1),
                'hours' => round($sixLossTotals['REDUCED_SPEED'] / 60.0, 2),
                'percentage' => $totalLossMins > 0 ? round(($sixLossTotals['REDUCED_SPEED'] / $totalLossMins) * 100, 1) : 0,
                'description' => 'Operating speed lower than designed ideal cycle time',
            ],
            [
                'loss' => 'Process Defects',
                'category' => 'Quality',
                'minutes' => round($sixLossTotals['PROCESS_DEFECTS'], 1),
                'hours' => round($sixLossTotals['PROCESS_DEFECTS'] / 60.0, 2),
                'percentage' => $totalLossMins > 0 ? round(($sixLossTotals['PROCESS_DEFECTS'] / $totalLossMins) * 100, 1) : 0,
                'description' => 'Time lost manufacturing reject products during steady-state',
            ],
            [
                'loss' => 'Reduced Yield (Scrap)',
                'category' => 'Quality',
                'minutes' => round($sixLossTotals['REDUCED_YIELD'], 1),
                'hours' => round($sixLossTotals['REDUCED_YIELD'] / 60.0, 2),
                'percentage' => $totalLossMins > 0 ? round(($sixLossTotals['REDUCED_YIELD'] / $totalLossMins) * 100, 1) : 0,
                'description' => 'Start-up scrap, purging, and warm-up material waste',
            ],
        ];

        // 8. Generate Major Incident Log Table
        $incidentLogs = [];
        $incCounter = 1;

        foreach ($downtimes as $dt) {
            $dur = (float) $dt->duration_minutes;
            if ($dur >= 1.0 || !empty($dt->description) || !empty($dt->action_taken)) {
                $timeWindow = ($dt->start_time ? $dt->start_time->format('H:i') : '00:00') . ' - ' . ($dt->end_time ? $dt->end_time->format('H:i') : '00:00');
                
                // Prioritize user's actual input for Root Cause / Masalah
                $rca = !empty($dt->description) 
                    ? $dt->description 
                    : ($dt->downtimeReason->name ?? 'Gangguan Operasional Mesin');

                // Prioritize user's actual input for Tindakan Perbaikan (CAPA)
                $capa = !empty($dt->action_taken) 
                    ? $dt->action_taken 
                    : (!empty($dt->description) ? "Tindakan perbaikan: {$dt->description}" : 'Penyetelan ulang dan penanganan oleh teknisi.');

                // Category problem type
                $category = !empty($dt->problem_type) 
                    ? $dt->problem_type 
                    : ($dt->is_planned ? 'Planned Maintenance' : ($dt->downtimeReason->name ?? 'Unplanned Downtime'));

                // PIC in-charge
                $pic = !empty($dt->team) 
                    ? "{$dt->team} Team" 
                    : ($dt->creator->name ?? ($dt->operator_name ?? 'Maintenance'));

                $incidentLogs[] = [
                    'incident_id' => 'INC-' . $startDate->format('md') . '-' . str_pad($incCounter++, 2, '0', STR_PAD_LEFT),
                    'time_window' => $timeWindow,
                    'machine_code' => $dt->machine->code ?? ($dt->machine->name ?? "MC-{$dt->machine_id}"),
                    'line_name' => $dt->productionLine->name ?? 'Production Line',
                    'category' => $category,
                    'duration_minutes' => $dur,
                    'root_cause' => $rca,
                    'action_plan' => $capa,
                    'pic' => $pic,
                    'status' => 'RESOLVED',
                ];
            }
        }

        // 9. Shift Handover Instructions Notes (derived from real production and incidents)
        $firstMachineCode = $prodRecords->first()?->machine?->code ?? 'MC-01';
        $handoverNotes = [];
        if (!empty($incidentLogs)) {
            foreach (array_slice($incidentLogs, 0, 3) as $inc) {
                $handoverNotes[] = "Mesin {$inc['machine_code']}: {$inc['root_cause']} ({$inc['duration_minutes']}m) — Tindakan: {$inc['action_plan']}. Mohon dipantau pada shift berikutnya.";
            }
        } else {
            $handoverNotes[] = "Semua mesin beroperasi normal tanpa kendala breakdown mayor selama shift berjalan (Zero Breakdown).";
            $handoverNotes[] = "Pastikan kebersihan area kerja (5S) dan penimbangan reject/scrap sebelum serah terima shift.";
        }

        return response()->json([
            'success' => true,
            'data' => [
                'header' => $header,
                'executive_summary' => $executiveSummary,
                'matrix_rows' => $matrixRows,
                'six_big_losses' => $sixLossList,
                'incident_logs' => $incidentLogs,
                'handover_notes' => $handoverNotes,
            ],
        ]);
    }

    /**
     * Get Comprehensive Trouble & Downtime Incident History Report
     */
    public function troubleSummary(Request $request): JsonResponse
    {
        $startDateStr = $request->get('start_date') ?: $request->get('date_from');
        $endDateStr = $request->get('end_date') ?: $request->get('date_to');
        $dateStr = $request->get('date');
        $shiftId = $request->get('shift_id');
        $lineId = $request->get('line_id');
        $machineId = $request->get('machine_id');
        $productId = $request->get('product_id');
        $period = $request->get('period');

        // Resolve Date Filter
        if ($startDateStr && $endDateStr) {
            $startDate = Carbon::parse($startDateStr)->startOfDay();
            $endDate = Carbon::parse($endDateStr)->endOfDay();
        } elseif ($startDateStr) {
            $startDate = Carbon::parse($startDateStr)->startOfDay();
            $endDate = Carbon::parse($startDateStr)->endOfDay();
        } elseif ($dateStr) {
            $startDate = Carbon::parse($dateStr)->startOfDay();
            $endDate = Carbon::parse($dateStr)->endOfDay();
        } elseif ($period === 'yesterday') {
            $startDate = Carbon::yesterday()->startOfDay();
            $endDate = Carbon::yesterday()->endOfDay();
        } elseif ($period === '7days') {
            $startDate = Carbon::now()->subDays(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } elseif ($period === '30days') {
            $startDate = Carbon::now()->subDays(29)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
        } else {
            $latestRecordDate = Downtime::max('start_time') ?: ProductionRecord::max('production_date');
            $startDate = $latestRecordDate ? Carbon::parse($latestRecordDate)->startOfDay() : Carbon::today()->startOfDay();
            $endDate = $latestRecordDate ? Carbon::parse($latestRecordDate)->endOfDay() : Carbon::today()->endOfDay();
        }

        // 1. Query Downtime trouble records with relations
        $downQuery = Downtime::with([
            'machine.workCenter.productionLine',
            'productionLine.plant',
            'shift',
            'product',
            'downtimeReason',
            'downtimeCategory',
            'creator',
            'productionRecord.product',
            'productionRecord.shift',
        ])->whereBetween('start_time', [$startDate, $endDate]);

        if ($lineId) {
            $downQuery->where('production_line_id', $lineId);
        }
        if ($machineId) {
            $downQuery->where('machine_id', $machineId);
        }
        if ($shiftId) {
            $downQuery->where('shift_id', $shiftId);
        }
        if ($productId) {
            $downQuery->where(function ($q) use ($productId) {
                $q->where('product_id', $productId)
                  ->orWhereHas('productionRecord', function ($pr) use ($productId) {
                      $pr->where('product_id', $productId);
                  });
            });
        }

        $downtimes = $downQuery->orderBy('start_time', 'desc')->get();

        // 2. Resolve Header Metadata
        $plant = Plant::first();
        $selectedShift = $shiftId ? Shift::find($shiftId) : null;
        $selectedLine = $lineId ? ProductionLine::find($lineId) : null;
        $selectedProduct = $productId ? \App\Models\Product::find($productId) : null;

        $requestedSupervisor = $request->get('supervisor');
        if (!empty($requestedSupervisor)) {
            $supervisor = trim($requestedSupervisor);
        } else {
            $group = $selectedLine ? \App\Models\Group::where('production_line_id', $selectedLine->id)->first() : \App\Models\Group::first();
            $supervisor = $group?->supervisor_name ?? $group?->leader_name ?? 'Alim Utama';
        }

        $profileRaw = \App\Models\SystemSetting::get('company_profile');
        $profile = is_array($profileRaw) ? $profileRaw : ($profileRaw ? json_decode($profileRaw, true) : []);

        $header = [
            'company_name' => $profile['company_name'] ?? 'PT. YASUNAGA INDONESIA',
            'company_logo' => !empty($profile['company_logo']) ? $profile['company_logo'] : '/images/yasunaga-logo.png',
            'plant_id' => $plant->code ?? 'PLT-01',
            'plant_name' => $plant->name ?? 'ENGINE PARTS & AIR PUMP MFG',
            'plant_location' => $plant->location ?? 'Kawasan Industri Modern Cikande, Serang - Banten',
            'address' => $profile['address'] ?? 'Jl. Modern Industri Raya Kav. 24 Kawasan Industri Modern Cikande, Nambo Ilir Kibin Serang Banten',
            'phone' => $profile['phone'] ?? '(0254) 400306',
            'email' => $profile['email'] ?? 'prodcr@yasunaga.co.id',
            'date_range_label' => $startDate->format('d M Y') . ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d') ? ' s/d ' . $endDate->format('d M Y') : ''),
            'production_date' => $startDate->format('Y-m-d'),
            'formatted_date' => $startDate->format('d M Y') . ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d') ? ' - ' . $endDate->format('d M Y') : ''),
            'shift_name' => $selectedShift ? $selectedShift->name : 'All Shifts (Consolidated)',
            'shift_time_window' => $selectedShift ? (Carbon::parse($selectedShift->start_time)->format('H:i') . ' - ' . Carbon::parse($selectedShift->end_time)->format('H:i') . ' WIB') : '24 Hours Operational Cycle',
            'line_name' => $selectedLine ? $selectedLine->name : 'All Production Lines',
            'product_name' => $selectedProduct ? "{$selectedProduct->name} ({$selectedProduct->sku})" : 'All Products / SKUs',
            'supervisor_in_charge' => $supervisor,
            'generated_at' => Carbon::now()->format('d M Y H:i') . ' WIB',
        ];

        // 3. Process trouble items & calculate KPIs
        $items = [];
        $totalMinutes = 0.0;
        $unplannedMinutes = 0.0;
        $plannedMinutes = 0.0;
        $unplannedCount = 0;
        $plannedCount = 0;
        $categoryBreakdown = [];
        $machineBreakdown = [];
        $counter = 1;

        foreach ($downtimes as $dt) {
            $dur = (float) $dt->calculated_duration_minutes;
            $totalMinutes += $dur;

            $isPlanned = (bool) $dt->is_planned;
            if ($isPlanned) {
                $plannedMinutes += $dur;
                $plannedCount++;
            } else {
                $unplannedMinutes += $dur;
                $unplannedCount++;
            }

            $dateObj = $dt->start_time ?: Carbon::today();
            $dateYmd = $dateObj->format('Y-m-d');
            $dateFmt = $dateObj->format('d M Y');
            $timeWindow = ($dt->start_time ? $dt->start_time->format('H:i') : '00:00') . ' - ' . ($dt->end_time ? $dt->end_time->format('H:i') : 'ONGOING');

            $probType = !empty($dt->problem_type) ? trim($dt->problem_type) : ($isPlanned ? 'Planned Maintenance' : 'Mesin');
            $reason = !empty($dt->downtimeReason->name) ? $dt->downtimeReason->name : ($dt->downtimeCategory->name ?? 'Gangguan Operasional Mesin');
            $rca = !empty($dt->description) ? $dt->description : $reason;
            $capa = !empty($dt->action_taken) ? $dt->action_taken : 'Penyetelan & perbaikan teknisi.';

            $mCode = $dt->machine->code ?? ($dt->machine->name ?? "MC-{$dt->machine_id}");
            $mName = $dt->machine->name ?? 'Standard Machine';
            $lName = $dt->productionLine->name ?? ($dt->machine->workCenter->productionLine->name ?? 'Production Line');
            $pName = $dt->product ? "{$dt->product->name} - {$dt->product->sku}" : ($dt->productionRecord->product ? "{$dt->productionRecord->product->name} - {$dt->productionRecord->product->sku}" : 'Standard Product');
            $sName = $dt->shift->name ?? ($dt->productionRecord->shift->name ?? 'Shift 1');
            $pic = !empty($dt->team) ? "{$dt->team} Team" : ($dt->creator->name ?? 'Maintenance');

            // Category aggregator
            if (!isset($categoryBreakdown[$probType])) {
                $categoryBreakdown[$probType] = ['category' => $probType, 'count' => 0, 'minutes' => 0.0];
            }
            $categoryBreakdown[$probType]['count']++;
            $categoryBreakdown[$probType]['minutes'] += $dur;

            // Machine pareto aggregator
            if (!isset($machineBreakdown[$mCode])) {
                $machineBreakdown[$mCode] = ['machine_code' => $mCode, 'machine_name' => $mName, 'line_name' => $lName, 'count' => 0, 'minutes' => 0.0];
            }
            $machineBreakdown[$mCode]['count']++;
            $machineBreakdown[$mCode]['minutes'] += $dur;

            $items[] = [
                'id' => $dt->id,
                'no' => $counter++,
                'incident_code' => 'TRB-' . $dateObj->format('ymd') . '-' . str_pad($dt->id, 3, '0', STR_PAD_LEFT),
                'production_date' => $dateYmd,
                'formatted_date' => $dateFmt,
                'start_time' => $dt->start_time ? $dt->start_time->format('H:i') : '-',
                'end_time' => $dt->end_time ? $dt->end_time->format('H:i') : 'ONGOING',
                'time_window' => $timeWindow,
                'line_id' => $dt->production_line_id,
                'line_name' => $lName,
                'machine_id' => $dt->machine_id,
                'machine_code' => $mCode,
                'machine_name' => $mName,
                'product_id' => $dt->product_id,
                'product_name' => $pName,
                'shift_id' => $dt->shift_id,
                'shift_name' => $sName,
                'problem_type' => $probType,
                'reason_name' => $reason,
                'root_cause' => $rca,
                'action_plan' => $capa,
                'duration_minutes' => $dur,
                'duration_hours' => round($dur / 60.0, 2),
                'is_planned' => $isPlanned,
                'type_label' => $isPlanned ? 'Planned Maintenance' : 'Unplanned Breakdown',
                'pic' => $pic,
                'status' => $dt->end_time ? 'RESOLVED' : 'ONGOING',
            ];
        }

        $totalIncidents = count($items);
        $totalHours = round($totalMinutes / 60.0, 1);
        $mttr = $totalIncidents > 0 ? round($totalMinutes / $totalIncidents, 1) : 0.0;
        $maxDowntime = $totalIncidents > 0 ? max(array_column($items, 'duration_minutes')) : 0.0;

        // Sort categories by minutes descending
        usort($categoryBreakdown, fn($a, $b) => $b['minutes'] <=> $a['minutes']);
        foreach ($categoryBreakdown as &$c) {
            $c['percentage'] = $totalMinutes > 0 ? round(($c['minutes'] / $totalMinutes) * 100, 1) : 0;
            $c['hours'] = round($c['minutes'] / 60.0, 1);
        }

        // Sort machines pareto by minutes descending
        usort($machineBreakdown, fn($a, $b) => $b['minutes'] <=> $a['minutes']);
        foreach ($machineBreakdown as &$m) {
            $m['percentage'] = $totalMinutes > 0 ? round(($m['minutes'] / $totalMinutes) * 100, 1) : 0;
            $m['hours'] = round($m['minutes'] / 60.0, 1);
        }

        $topBottleneckMachine = !empty($machineBreakdown) ? $machineBreakdown[0] : null;
        $dominantCategory = !empty($categoryBreakdown) ? $categoryBreakdown[0] : null;

        $summary = [
            'total_incidents' => $totalIncidents,
            'total_downtime_minutes' => round($totalMinutes, 1),
            'total_downtime_hours' => $totalHours,
            'mttr_minutes' => $mttr,
            'max_downtime_minutes' => round($maxDowntime, 1),
            'unplanned_count' => $unplannedCount,
            'unplanned_minutes' => round($unplannedMinutes, 1),
            'unplanned_hours' => round($unplannedMinutes / 60.0, 1),
            'planned_count' => $plannedCount,
            'planned_minutes' => round($plannedMinutes, 1),
            'planned_hours' => round($plannedMinutes / 60.0, 1),
            'top_machine' => $topBottleneckMachine,
            'dominant_category' => $dominantCategory,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'header' => $header,
                'summary' => $summary,
                'items' => $items,
                'category_breakdown' => array_values($categoryBreakdown),
                'top_machines' => array_values(array_slice($machineBreakdown, 0, 5)),
            ],
        ]);
    }

    /**
     * Export Standard Dataset
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'excel'); // excel, csv, json
        $kind = $request->get('kind', 'shift_handover'); // shift_handover, oee, production, downtime, quality

        if ($type === 'csv') {
            return $this->exportCsv($kind, $request);
        }

        if ($type === 'excel' || $type === 'xls') {
            return $this->exportExcel($request);
        }

        return $this->exportJsonData($kind, $request);
    }

    private function exportExcel(Request $request)
    {
        $summaryResponse = $this->shiftSummary($request);
        $reportData = json_decode($summaryResponse->getContent(), true)['data'] ?? [];

        $header = $reportData['header'] ?? [];
        $summary = $reportData['executive_summary'] ?? [];
        $rows = $reportData['matrix_rows'] ?? [];
        $sixLosses = $reportData['six_big_losses'] ?? [];
        $incidentLogs = $reportData['incident_logs'] ?? [];
        $handoverNotes = $reportData['handover_notes'] ?? [];

        $fileNameDate = $header['production_date'] ?? date('Y-m-d');
        $fileNameShift = preg_replace('/\s+/', '_', $header['shift_name'] ?? 'Shift_All');
        $filename = "Laporan_OEE_Yasunaga_{$fileNameDate}_{$fileNameShift}.xls";

        $html = view('exports.shift_handover_excel', compact(
            'header',
            'summary',
            'rows',
            'sixLosses',
            'incidentLogs',
            'handoverNotes'
        ))->render();

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function exportJsonData(string $kind, Request $request): JsonResponse
    {
        $data = match ($kind) {
            'production' => ProductionRecord::with(['productionLine', 'machine', 'product', 'shift'])->limit(500)->get(),
            'downtime' => Downtime::with(['machine', 'downtimeReason'])->limit(500)->get(),
            'quality' => QualityRecord::with(['product', 'defectReason'])->limit(500)->get(),
            default => OeeRecord::with(['productionLine', 'machine', 'shift'])->limit(500)->get(),
        };

        return response()->json([
            'success' => true,
            'report_kind' => $kind,
            'total_rows' => $data->count(),
            'data' => $data,
        ]);
    }

    private function exportCsv(string $kind, Request $request): StreamedResponse
    {
        $filename = "OEE_Sys_{$kind}_Report_" . date('Y-m-d_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($kind) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($kind === 'shift_handover' || $kind === 'oee') {
                fputcsv($file, ['Line ID', 'Line Name', 'Machine Code', 'Machine Name', 'Produk - SKU', 'Planned (Min)', 'Downtime (Min)', 'Availability (%)', 'Target Speed (Pcs/Min)', 'Total Output (Pcs)', 'Performance (%)', 'Defect (Pcs)', 'Quality (%)', 'OEE Score (%)', 'Status Classification']);
                ProductionRecord::with(['productionLine', 'machine', 'product'])->chunk(100, function ($records) use ($file) {
                    foreach ($records as $r) {
                        $avail = $r->planned_production_time > 0 ? round(($r->run_time / $r->planned_production_time) * 100, 2) : 0.0;
                        $perf = ($r->run_time > 0 && $r->ideal_cycle_time > 0) ? round((($r->total_quantity * $r->ideal_cycle_time) / ($r->run_time * 60.0)) * 100, 2) : 0.0;
                        $qual = $r->total_quantity > 0 ? round(($r->good_quantity / $r->total_quantity) * 100, 2) : 100.0;
                        $oee = round(($avail * $perf * $qual) / 10000.0, 2);
                        $status = $oee >= 85.0 ? 'OPTIMAL (WORLD CLASS)' : ($oee >= 65.0 ? 'WARNING (MINOR LOSS)' : 'CRITICAL (BREAKDOWN)');

                        fputcsv($file, [
                            "LIN-0{$r->production_line_id}",
                            $r->productionLine->name ?? 'N/A',
                            $r->machine->code ?? 'N/A',
                            $r->machine->name ?? 'N/A',
                            $r->product ? "{$r->product->name} - {$r->product->sku}" : 'N/A',
                            $r->planned_production_time,
                            $r->downtime,
                            $avail . '%',
                            $r->ideal_cycle_time > 0 ? round(60.0 / $r->ideal_cycle_time, 1) : 0,
                            $r->total_quantity,
                            $perf . '%',
                            $r->reject_quantity + $r->scrap_quantity,
                            $qual . '%',
                            $oee . '%',
                            $status,
                        ]);
                    }
                });
            } elseif ($kind === 'production') {
                fputcsv($file, ['ID', 'Date', 'Line', 'Machine', 'Produk - SKU', 'Target Qty', 'Actual Qty', 'Good Qty', 'Reject Qty', 'Downtime (min)', 'Run Time (min)']);
                ProductionRecord::with(['productionLine', 'machine', 'product'])->chunk(100, function ($records) use ($file) {
                    foreach ($records as $r) {
                        fputcsv($file, [
                            $r->id,
                            $r->production_date ? $r->production_date->format('Y-m-d') : '',
                            $r->productionLine->name ?? 'N/A',
                            $r->machine->code ?? 'N/A',
                            $r->product ? "{$r->product->name} - {$r->product->sku}" : 'N/A',
                            $r->target_quantity,
                            $r->total_quantity,
                            $r->good_quantity,
                            $r->reject_quantity,
                            $r->downtime,
                            $r->run_time,
                        ]);
                    }
                });
            } elseif ($kind === 'downtime') {
                fputcsv($file, ['ID', 'Start Time', 'End Time', 'Line', 'Machine', 'Reason', 'Duration (min)', 'Is Planned']);
                Downtime::with(['productionLine', 'machine', 'downtimeReason'])->chunk(100, function ($records) use ($file) {
                    foreach ($records as $r) {
                        fputcsv($file, [
                            $r->id,
                            $r->start_time ? $r->start_time->format('Y-m-d H:i:s') : '',
                            $r->end_time ? $r->end_time->format('Y-m-d H:i:s') : 'ONGOING',
                            $r->productionLine->name ?? 'N/A',
                            $r->machine->code ?? 'N/A',
                            $r->downtimeReason->name ?? 'N/A',
                            $r->duration_minutes,
                            $r->is_planned ? 'YES' : 'NO',
                        ]);
                    }
                });
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

