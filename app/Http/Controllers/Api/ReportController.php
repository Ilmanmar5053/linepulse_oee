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
            'items.product',
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
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_time', [$startDate, $endDate])
                  ->orWhere(function ($sq) use ($startDate, $endDate) {
                      $sq->whereNull('start_time')
                         ->whereHas('productionRecord', fn($pr) => $pr->whereBetween('production_date', [substr($startDate, 0, 10), substr($endDate, 0, 10)]));
                  })
                  ->orWhere(function ($sq) use ($startDate, $endDate) {
                      $sq->whereNull('start_time')
                         ->whereNull('production_record_id')
                         ->whereBetween('created_at', [$startDate, $endDate]);
                  });
            });
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
            'report_logo' => !empty($profile['report_logo']) ? $profile['report_logo'] : (!empty($profile['company_logo']) ? $profile['company_logo'] : '/images/yasunaga-logo.png'),
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

            $hasMultiSku = $record->items && $record->items->count() > 1;
            if ($hasMultiSku) {
                $skuNames = $record->items->map(fn($it) => $it->product?->name)->filter()->unique()->values()->join(' & ');
                $skuCodes = $record->items->map(fn($it) => $it->product?->sku ?? $it->product?->name)->filter()->unique()->values()->join(' & ');
                $prodTitle = $skuNames ?: ($record->product?->name ?? 'Multi-Product');
                $prodSku = $skuCodes ?: ($record->product?->sku ?? 'Multi-SKU');
            } else {
                $prodTitle = $record->product?->name ?? 'Standard Product';
                $prodSku = $record->product ? "{$record->product->name} - {$record->product->sku}" : ($record->product->sku ?? 'Standard Product - SKU-GEN-001');
            }

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
                'product_sku' => $prodSku,
                'product_name' => $prodTitle,
                'has_multi_sku' => $hasMultiSku,
                'sku_breakdown' => $record->items ? $record->items->map(fn($it) => [
                    'product_id' => $it->product_id,
                    'product_name' => $it->product?->name ?? "Product #{$it->product_id}",
                    'product_sku' => $it->product?->sku ?? "SKU #{$it->product_id}",
                    'total_quantity' => (int) $it->total_quantity,
                    'good_quantity' => (int) $it->good_quantity,
                    'reject_quantity' => (int) $it->reject_quantity,
                ])->values() : [],
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
        $lineId = $request->get('line_id') ?: $request->get('production_line_id');
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
        ])->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_time', [$startDate, $endDate])
              ->orWhere(function ($sq) use ($startDate, $endDate) {
                  $sq->whereNull('start_time')
                     ->whereHas('productionRecord', fn($pr) => $pr->whereBetween('production_date', [substr($startDate, 0, 10), substr($endDate, 0, 10)]));
              })
              ->orWhere(function ($sq) use ($startDate, $endDate) {
                  $sq->whereNull('start_time')
                     ->whereNull('production_record_id')
                     ->whereBetween('created_at', [$startDate, $endDate]);
              });
        });

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
            'report_logo' => !empty($profile['report_logo']) ? $profile['report_logo'] : (!empty($profile['company_logo']) ? $profile['company_logo'] : '/images/yasunaga-logo.png'),
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

            $probType = !empty($dt->problem_type) ? trim($dt->problem_type) : ($isPlanned ? 'Planned Downtime' : 'Problem Mesin Mekanik');
            $problem = !empty($dt->description) ? $dt->description : '-';
            $cause = !empty($dt->cause) ? $dt->cause : '-';
            $capa = !empty($dt->action_taken) ? $dt->action_taken : 'Penyetelan & perbaikan teknisi.';

            $mCode = $dt->machine->code ?? ($dt->machine->name ?? "MC-{$dt->machine_id}");
            $mName = $dt->machine->name ?? 'Standard Machine';
            $lName = $dt->productionLine->name ?? ($dt->machine->workCenter->productionLine->name ?? 'Production Line');
            $pName = $dt->product ? "{$dt->product->name} - {$dt->product->sku}" : ($dt->productionRecord->product ? "{$dt->productionRecord->product->name} - {$dt->productionRecord->product->sku}" : 'Standard Product');
            $sName = $dt->shift->name ?? ($dt->productionRecord->shift->name ?? 'Shift 1');
            $pic = !empty($dt->pic) ? $dt->pic : (!empty($dt->team) ? "{$dt->team} Team" : ($dt->creator->name ?? 'Maintenance'));
            $status = !empty($dt->status) ? strtoupper($dt->status) : ($dt->end_time ? 'CLOSED' : 'OPEN');

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
                'team' => $dt->team,
                'problem_type' => $probType,
                'description' => $problem,
                'cause' => $cause,
                'reason_name' => $cause !== '-' ? $cause : '',
                'root_cause' => $cause !== '-' ? $cause : ($problem !== '-' ? $problem : '-'),
                'action_plan' => $capa,
                'duration_minutes' => $dur,
                'duration_hours' => round($dur / 60.0, 2),
                'is_planned' => $isPlanned,
                'type_label' => $isPlanned ? 'Planned Downtime' : 'Unplanned Breakdown',
                'pic' => $pic,
                'status' => $status,
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
     * Get Official Yasunaga Line Production Ratio Report (Form: F-PROD/CR-030/R0)
     * Matches layout: Monthly Ratio Chart + 14-Col Grid + Type Breakdown + 3 Problem Categories (Mesin, Kualitas, Tool)
     */
    public function lineProductionRatioReport(Request $request): JsonResponse
    {
        $lineId = $request->get('line_id') ?: $request->get('production_line_id');
        $year = (int) ($request->get('year') ?: Carbon::now()->year);
        $month = $request->get('month') ? (int) $request->get('month') : null;
        $period = $request->get('period');

        // Resolve Line
        $productionLine = $lineId ? ProductionLine::with('plant')->find($lineId) : ProductionLine::with('plant')->first();
        $lineId = $productionLine?->id;

        // Company Profile & Settings
        $profileRaw = \App\Models\SystemSetting::get('company_profile');
        $profile = is_array($profileRaw) ? $profileRaw : ($profileRaw ? json_decode($profileRaw, true) : []);
        $plant = $productionLine?->plant ?? Plant::first();

        // Line display name (e.g. CON ROD FLEX. 1)
        $lineCode = $productionLine?->code ?? 'FX-1';
        $lineName = $productionLine?->name ?? 'FX-1';
        $lineFullLabel = (stripos($lineName, 'CON ROD') !== false || stripos($lineCode, 'FX') === false) 
            ? $lineName 
            : "CON ROD FLEX. " . str_ireplace(['FX-', 'FX '], '', $lineCode);

        // Supervisors & Sign-offs (Dibuat, Dicheck, Disetujui)
        $group = $productionLine ? \App\Models\Group::where('production_line_id', $productionLine->id)->first() : \App\Models\Group::first();

        // 1. Dibuat oleh (default leader / creator)
        $requestedCreator = $request->get('dibuat_oleh') ?: ($request->get('group_leader') ?: $request->get('creator'));
        $groupLeader = !empty($requestedCreator) ? trim($requestedCreator) : ($group?->leader_name ?? 'Budi Santoso');

        // 2. Dicheck oleh (default supervisor)
        $requestedSupervisor = $request->get('dicheck_oleh') ?: $request->get('supervisor');
        $supervisor = !empty($requestedSupervisor) ? trim($requestedSupervisor) : ($group?->supervisor_name ?? 'Alim Utama');

        // 3. Disetujui oleh (default manager)
        $requestedManager = $request->get('disetujui_oleh') ?: $request->get('manager');
        $manager = !empty($requestedManager) ? trim($requestedManager) : ($group?->manager_name ?? 'Arief R');

        // Dynamic Options lookup for Line Performances signatures:
        // 1. Dibuat oleh: All registered user accounts in the system
        $allUsers = \App\Models\User::orderBy('name')->pluck('name');
        $allGroupLeaders = \App\Models\Group::whereNotNull('leader_name')->pluck('leader_name');
        $allCreatorsList = $allUsers
            ->merge($allGroupLeaders)
            ->merge(['Budi Santoso', 'Agus Setiawan', 'Bambang Haryanto'])
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        // 2. Dicheck oleh: Registered supervisor user accounts
        $supervisorUsers = \App\Models\User::whereHas('roles', function ($q) {
                $q->where('name', 'like', '%supervisor%')
                  ->orWhere('display_name', 'like', '%supervisor%');
            })
            ->pluck('name');
        $allGroupSupervisors = \App\Models\Group::whereNotNull('supervisor_name')->pluck('supervisor_name');
        $supervisorsList = $supervisorUsers
            ->merge($allGroupSupervisors)
            ->merge(['Alim Utama', 'Hendra Wijaya', 'Surya Pratama', 'Dona I'])
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        // 3. Disetujui oleh: Registered manager user accounts
        $managerUsers = \App\Models\User::whereHas('roles', function ($q) {
                $q->where('name', 'like', '%manager%')
                  ->orWhere('display_name', 'like', '%manager%')
                  ->orWhereIn('name', ['super_admin', 'management']);
            })
            ->pluck('name');
        $managersList = $managerUsers
            ->merge(['Arief R', 'Plant Manager'])
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        // Dynamic Target Ratio per Line from Master Data (separate from target_oee, default 83.0%)
        $targetRatioStandard = (float) (($productionLine && $productionLine->target_ratio > 0) ? $productionLine->target_ratio : 83.0);

        // 1. QUERY ALL PRODUCTION RECORDS FOR THE GIVEN YEAR (AND PREVIOUS YEAR FOR BENCHMARK)
        $yearRecords = ProductionRecord::with(['product', 'items.product', 'machine', 'shift'])
            ->whereYear('production_date', $year)
            ->when($lineId, fn($q) => $q->where('production_line_id', $lineId))
            ->get();

        $prevYearRecords = ProductionRecord::whereYear('production_date', $year - 1)
            ->when($lineId, fn($q) => $q->where('production_line_id', $lineId))
            ->get();

        // Benchmark (Prev Year Average)
        $bmPlanPpic = $prevYearRecords->count() > 0 ? round($prevYearRecords->avg('target_quantity')) : 5490;
        $bmPlanProd = $prevYearRecords->count() > 0 ? round($prevYearRecords->avg('target_quantity')) : 5490;
        $bmActProd = $prevYearRecords->count() > 0 ? round($prevYearRecords->avg('total_quantity')) : 5621;
        $bmRatio = $bmPlanProd > 0 ? round(($bmActProd / $bmPlanProd) * 100, 2) : 76.00;

        // Build 12 Months Summary Array
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyCols = [];
        $annualPpicSum = 0;
        $annualProdSum = 0;
        $annualActSum = 0;
        $activeMonthCount = 0;

        for ($m = 1; $m <= 12; $m++) {
            $mKey = $monthNames[$m - 1];
            $mRecords = $yearRecords->filter(function ($r) use ($m) {
                return Carbon::parse($r->production_date)->month === $m;
            });

            $hasData = $mRecords->count() > 0;
            if ($hasData) {
                $mPlanPpic = (int) $mRecords->sum('target_quantity');
                $mActProd = (int) $mRecords->sum('total_quantity');
                
                // Plan Prod: sinkron dengan target produksi yang disepakati (target_quantity), atau kapasitas floor per shift
                $mPlanProd = $mPlanPpic > 0 ? $mPlanPpic : (int) $mRecords->sum(function ($r) {
                    $ict = (float) $r->ideal_cycle_time;
                    $ppt = (float) $r->planned_production_time;
                    return ($ict > 0 && $ppt > 0) ? (int) floor(($ppt * 60) / $ict) : (int) ($r->target_quantity ?: 0);
                });
                if ($mPlanProd <= 0) {
                    $mPlanProd = $mPlanPpic;
                }

                $mRatio = $mPlanProd > 0 ? round(($mActProd / $mPlanProd) * 100, 2) : 0.0;

                $annualPpicSum += $mPlanPpic;
                $annualProdSum += $mPlanProd;
                $annualActSum += $mActProd;
                $activeMonthCount++;

                $monthlyCols[] = [
                    'month_num' => $m,
                    'month_label' => $mKey,
                    'has_data' => true,
                    'plan_ppic' => $mPlanPpic,
                    'plan_prod' => $mPlanProd,
                    'actual_prod' => $mActProd,
                    'rasio_produksi' => $mRatio,
                    'target' => $targetRatioStandard,
                ];
            } else {
                $monthlyCols[] = [
                    'month_num' => $m,
                    'month_label' => $mKey,
                    'has_data' => false,
                    'plan_ppic' => null,
                    'plan_prod' => null,
                    'actual_prod' => null,
                    'rasio_produksi' => null,
                    'target' => $targetRatioStandard,
                ];
            }
        }

        // Annual YTD Average
        $avgYearPpic = $activeMonthCount > 0 ? round($annualPpicSum / $activeMonthCount) : 0;
        $avgYearProd = $activeMonthCount > 0 ? round($annualProdSum / $activeMonthCount) : 0;
        $avgYearAct = $activeMonthCount > 0 ? round($annualActSum / $activeMonthCount) : 0;
        $avgYearRatio = $avgYearProd > 0 ? round(($avgYearAct / $avgYearProd) * 100, 2) : 0.0;

        $monthlyGrid = [
            'months' => $monthlyCols,
            'ytd_average' => [
                'label' => "TH {$year}\n(RATA-RATA)",
                'plan_ppic' => $avgYearPpic,
                'plan_prod' => $avgYearProd,
                'actual_prod' => $avgYearAct,
                'rasio_produksi' => $avgYearRatio,
                'target' => $targetRatioStandard,
            ],
        ];

        // 2. PRODUCT TYPE BREAKDOWN FOR SELECTED MONTH (OR LATEST ACTIVE MONTH)
        $selectedMonth = $month ?: ($yearRecords->max(fn($r) => Carbon::parse($r->production_date)->month) ?: Carbon::now()->month);
        $monthProdRecords = $yearRecords->filter(function ($r) use ($selectedMonth) {
            return Carbon::parse($r->production_date)->month === $selectedMonth;
        });

        // Build product-level buckets from records and multi-SKU items
        $productBuckets = [];

        foreach ($monthProdRecords as $r) {
            if ($r->items && $r->items->count() > 0) {
                $recTotal = (float) ($r->total_quantity ?: ($r->items->sum('total_quantity') ?: 1));
                foreach ($r->items as $it) {
                    $pId = $it->product_id ?: $r->product_id;
                    if (!isset($productBuckets[$pId])) {
                        $productBuckets[$pId] = [
                            'product' => $it->product ?: ($r->product?->id == $pId ? $r->product : \App\Models\Product::find($pId)),
                            'plan_ppic' => 0,
                            'act_prod' => 0,
                            'plan_mins' => 0.0,
                            'act_mins' => 0.0,
                            'active_days' => [],
                        ];
                    }
                    $itemAct = (int) ($it->total_quantity ?: ($it->good_quantity + $it->reject_quantity));
                    $ratio = $recTotal > 0 ? ($itemAct / $recTotal) : (1.0 / $r->items->count());
                    $itemTarget = $it->target_quantity > 0 ? (int) $it->target_quantity : (int) round(($r->target_quantity ?: 0) * $ratio);

                    $productBuckets[$pId]['plan_ppic'] += $itemTarget;
                    $productBuckets[$pId]['act_prod'] += $itemAct;

                    $planMins = (float) $r->planned_production_time > 0 ? (float) $r->planned_production_time : ((float) $r->available_production_time ?: 444);
                    $actMins = (float) $r->run_time > 0 ? (float) $r->run_time : max(0, $planMins - (float) $r->downtime);

                    $productBuckets[$pId]['plan_mins'] += ($planMins * $ratio);
                    $productBuckets[$pId]['act_mins'] += ($actMins * $ratio);
                    $productBuckets[$pId]['active_days'][] = Carbon::parse($r->production_date)->day;
                }
            } else {
                $pId = $r->product_id;
                if (!isset($productBuckets[$pId])) {
                    $productBuckets[$pId] = [
                        'product' => $r->product ?: \App\Models\Product::find($pId),
                        'plan_ppic' => 0,
                        'act_prod' => 0,
                        'plan_mins' => 0.0,
                        'act_mins' => 0.0,
                        'active_days' => [],
                    ];
                }
                $productBuckets[$pId]['plan_ppic'] += (int) $r->target_quantity;
                $productBuckets[$pId]['act_prod'] += (int) $r->total_quantity;
                $planMins = (float) $r->planned_production_time > 0 ? (float) $r->planned_production_time : ((float) $r->available_production_time ?: 444);
                $actMins = (float) $r->run_time > 0 ? (float) $r->run_time : max(0, $planMins - (float) $r->downtime);
                $productBuckets[$pId]['plan_mins'] += $planMins;
                $productBuckets[$pId]['act_mins'] += $actMins;
                $productBuckets[$pId]['active_days'][] = Carbon::parse($r->production_date)->day;
            }
        }

        $typeRows = [];
        $totalTypePlanPpic = 0;
        $totalTypePlanProd = 0;
        $totalTypeActProd = 0;
        $totalTypePlanJk = 0.0;
        $totalTypeActJk = 0.0;

        foreach ($productBuckets as $pId => $b) {
            $product = $b['product'];
            $planPpic = $b['plan_ppic'];
            $actProd = $b['act_prod'];

            if ($planPpic == 0 && $actProd == 0) {
                continue;
            }

            $cycleTime = (float) ($product?->ideal_cycle_time ?: 21);
            $planProd = $planPpic > 0 ? $planPpic : (int) floor(($b['plan_mins'] * 60) / ($cycleTime > 0 ? $cycleTime : 21));

            $planJk = round($b['plan_mins'] / 60.0, 1);
            $actJk = round($b['act_mins'] / 60.0, 1);
            $activeDays = array_values(array_unique($b['active_days']));
            $tglProduksi = $this->formatDayRanges($activeDays);

            $actRatio = $planProd > 0 ? round(($actProd / $planProd) * 100, 2) : 0.0;

            $totalTypePlanPpic += $planPpic;
            $totalTypePlanProd += $planProd;
            $totalTypeActProd += $actProd;
            $totalTypePlanJk += $planJk;
            $totalTypeActJk += $actJk;

            $typeRows[] = [
                'type' => $product?->name ?: ($product?->sku ?: "TYPE #{$pId}"),
                'sku' => $product?->sku,
                'tgl_produksi' => $tglProduksi,
                'cycle_time' => $cycleTime,
                'plan_ppic' => $planPpic,
                'plan_prod' => $planProd,
                'act_prod' => $actProd,
                'plan_jk' => $planJk,
                'act_jk' => $actJk,
                'plan_rasio' => $targetRatioStandard,
                'act_rasio' => $actRatio,
            ];
        }

        $totalTypeActRatio = $totalTypePlanProd > 0 ? round(($totalTypeActProd / $totalTypePlanProd) * 100, 2) : 0.0;
        $typeTotalRow = [
            'type' => 'TOTAL',
            'tgl_produksi' => '-',
            'cycle_time' => count($typeRows) > 0 ? round(array_sum(array_column($typeRows, 'cycle_time')) / count($typeRows), 1) : 21,
            'plan_ppic' => $totalTypePlanPpic,
            'plan_prod' => $totalTypePlanProd,
            'act_prod' => $totalTypeActProd,
            'plan_jk' => round($totalTypePlanJk, 1),
            'act_jk' => round($totalTypeActJk, 1),
            'plan_rasio' => $targetRatioStandard,
            'act_rasio' => $totalTypeActRatio,
        ];

        // 3. CATEGORIZED PROBLEMS (MESIN, KUALITAS / QUALITY, TOOL)
        $monthStart = Carbon::create($year, $selectedMonth, 1)->startOfMonth();
        $monthEnd = Carbon::create($year, $selectedMonth, 1)->endOfMonth();

        $downtimeQuery = Downtime::with(['machine', 'product', 'downtimeReason', 'downtimeCategory'])
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('start_time', [$monthStart, $monthEnd])
                  ->orWhere(function ($sq) use ($monthStart, $monthEnd) {
                      $sq->whereNull('start_time')
                         ->whereHas('productionRecord', fn($pr) => $pr->whereBetween('production_date', [$monthStart->toDateString(), $monthEnd->toDateString()]));
                  })
                  ->orWhere(function ($sq) use ($monthStart, $monthEnd) {
                      $sq->whereNull('start_time')
                         ->whereNull('production_record_id')
                         ->whereBetween('created_at', [$monthStart, $monthEnd]);
                  });
            })
            ->when($lineId, fn($q) => $q->where('production_line_id', $lineId));
        $monthDowntimes = $downtimeQuery->orderBy('start_time', 'asc')->get();

        $ngQuery = \App\Models\NgRecord::with(['machine', 'product', 'defectReason', 'items'])
            ->whereBetween('production_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->whereHas('productionRecord', fn($pr) => $pr->where('production_line_id', $lineId));
        $monthNgRecords = $ngQuery->get();

        $problemsMesin = [];
        $problemsKualitas = [];
        $problemsTool = [];

        // Helper to extract clean OP number / name
        $extractOp = function ($obj) {
            $desc = $obj->description ?? ($obj->defect_symptom ?? '');
            $cause = $obj->cause ?? ($obj->notes ?? '');
            $action = $obj->action_taken ?? '';
            $combinedText = $desc . ' ' . $cause . ' ' . $action;

            // 1. Problem notes mention explicit OP
            if (preg_match('/\b(OP[- .]?\d+[A-Z]?)\b/i', $combinedText, $m)) {
                return strtoupper(str_replace([' ', '.'], '-', $m[1]));
            }

            $mName = trim($obj->machine?->name ?? '');
            $mCode = trim($obj->machine?->code ?? '');

            // 2. Machine name is OP-10, OP-20, OP-80, etc.
            if (preg_match('/^OP[- ]?\d+[A-Z]?/i', $mName, $m)) {
                return strtoupper(str_replace(' ', '-', $m[0]));
            }

            // 3. Machine code contains numeric OP (e.g. MC-FX-110 -> OP-10, MC-FX-180 -> OP-80, MC-FX-125 -> OP-25)
            if (preg_match('/MC[- ]?[A-Z0-9]*[- ]?(\d)(\d{2})/i', $mCode, $m)) {
                return 'OP-' . intval($m[2]);
            }

            if (preg_match('/OP[- .]?\d+/i', $mCode, $m)) {
                return strtoupper(str_replace([' ', '.'], '-', $m[0]));
            }

            if (stripos($mName, 'meas') !== false || stripos($mCode, 'meas') !== false) {
                return 'OP-MEAS';
            }

            return $mName ?: ($mCode ?: 'OP-01');
        };

        // Helper to resolve status symbol
        $resolveStatusSymbol = function ($status) {
            $s = strtoupper((string) $status);
            if (in_array($s, ['CLOSED', 'RESOLVED', 'COMPLETED', 'PERMANENT', 'DONE'])) {
                return '○'; // Sudah perbaikan permanen
            } elseif (in_array($s, ['MONITORING', 'IN_PROGRESS', 'PARTIAL', 'FOLLOW_UP'])) {
                return '△'; // Hasil perbaikan masih dipantau
            }
            return 'x'; // Belum perbaikan secara permanen
        };

        foreach ($monthDowntimes as $dt) {
            $opNumber = $extractOp($dt);
            $typeName = $dt->product?->name ?? 'RT 140';
            $durHours = round(($dt->duration_minutes ?: $dt->calculated_duration_minutes) / 60.0, 1) . ' H';
            $probText = $dt->description ?: ($dt->downtimeReason?->name ?: 'Gangguan Mesin');
            $causeText = $dt->cause ?: '-';
            $capaText = $dt->action_taken ?: 'Penyetelan & kalibrasi ulang';
            $pic = $dt->pic ?: (!empty($dt->team) ? $dt->team : 'MTC');
            $hasil = $resolveStatusSymbol($dt->status);

            $pTypeLower = strtolower($dt->problem_type . ' ' . $dt->description . ' ' . $dt->cause . ' ' . $dt->downtimeCategory?->name);

            $entry = [
                'id' => $dt->id,
                'op' => $opNumber,
                'type' => $typeName,
                'masalah' => $probText,
                'penyebab' => $causeText,
                'perbaikan' => $capaText,
                'pic' => $pic,
                'lama_stop' => $durHours,
                'hasil' => $hasil,
                'perbaikan_selanjutnya' => !empty($dt->follow_up_action) ? $dt->follow_up_action : '-',
                'due_date' => !empty($dt->due_date) ? Carbon::parse($dt->due_date)->format('d/m/Y') : '-',
                'pic_followup' => !empty($dt->pic_followup) ? $dt->pic_followup : '-',
            ];

            if (str_contains($pTypeLower, 'tool') || str_contains($pTypeLower, 'cutter') || str_contains($pTypeLower, 'insert') || str_contains($pTypeLower, 'sparol') || str_contains($pTypeLower, 'regrinding')) {
                $entry['pic'] = $dt->pic ?: 'TS';
                $problemsTool[] = $entry;
            } elseif (str_contains($pTypeLower, 'kualitas') || str_contains($pTypeLower, 'quality') || str_contains($pTypeLower, 'defect') || str_contains($pTypeLower, 'ng ') || str_contains($pTypeLower, 'visual') || str_contains($pTypeLower, 'awasemen')) {
                $entry['pic'] = $dt->pic ?: 'PE';
                $problemsKualitas[] = $entry;
            } else {
                $entry['pic'] = $dt->pic ?: 'MTC';
                $problemsMesin[] = $entry;
            }
        }

        // Add NG items to Kualitas problem table if any
        foreach ($monthNgRecords as $ng) {
            $firstItem = $ng->items?->first();
            $opNumber = !empty($firstItem?->op_machine) ? $firstItem->op_machine : $extractOp($ng);
            $typeName = $ng->product?->name ?? 'RT 140';
            $masalah = $ng->defect_symptom ?: ($firstItem?->reason ?: ($ng->defectReason?->name ?: 'Cacat Kualitas'));
            $penyebab = $firstItem?->section ? "Bagian: {$firstItem->section}" . ($ng->notes ? " ({$ng->notes})" : '') : ($ng->notes ?: '-');
            $perbaikan = $ng->action_taken ?: 'Penyetelan jig dan parameter proses';
            $pic = $ng->inspector_name ?: 'PE';

            $problemsKualitas[] = [
                'id' => 'NG-' . $ng->id,
                'op' => $opNumber,
                'type' => $typeName,
                'masalah' => $masalah,
                'penyebab' => $penyebab,
                'perbaikan' => $perbaikan,
                'pic' => $pic,
                'lama_stop' => '-',
                'hasil' => '○',
                'perbaikan_selanjutnya' => !empty($ng->follow_up_action) ? $ng->follow_up_action : '-',
                'due_date' => !empty($ng->due_date) ? Carbon::parse($ng->due_date)->format('d/m/Y') : '-',
                'pic_followup' => !empty($ng->pic_followup) ? $ng->pic_followup : '-',
            ];
        }

        $header = [
            'company_name' => $profile['company_name'] ?? 'PT. YASUNAGA INDONESIA',
            'division' => 'DIV. PRODUCTION CON - ROD',
            'company_logo' => !empty($profile['company_logo']) ? $profile['company_logo'] : '/images/yasunaga-logo.png',
            'report_logo' => !empty($profile['report_logo']) ? $profile['report_logo'] : (!empty($profile['company_logo']) ? $profile['company_logo'] : '/images/yasunaga-logo.png'),
            'form_number' => 'F-PROD/CR-030/R0',
            'target_ratio' => $targetRatioStandard,
            'target_ratio_badge' => "{$targetRatioStandard}%",
            'target_oee' => (float) ($productionLine?->target_oee ?? 85.0),
            'title' => "GRAFIK RASIO PRODUKSI {$lineFullLabel}",
            'year_title' => "TH. {$year}",
            'line_id' => $lineId,
            'line_code' => $lineCode,
            'line_name' => $lineName,
            'line_full_label' => $lineFullLabel,
            'selected_year' => $year,
            'selected_month' => $selectedMonth,
            'selected_month_label' => $monthNames[$selectedMonth - 1] ?? 'Bulan',
            'date_formatted' => Carbon::now()->format('d F Y'),
            'date_iso' => Carbon::now()->format('Y-m-d'),
        ];

        $signatures = [
            'date_label' => 'Tanggal : ' . Carbon::now()->format('d F Y'),
            'disetujui' => [
                'title' => 'Disetujui oleh',
                'label' => 'Disetujui oleh:',
                'subtitle' => 'Diketahui / Manager',
                'role' => 'Factory Manager',
                'name' => $manager,
            ],
            'dicheck' => [
                'title' => 'Dicheck oleh',
                'label' => 'Dicheck oleh:',
                'subtitle' => 'Group SPV',
                'role' => 'Supervisor',
                'name' => $supervisor,
            ],
            'dibuat' => [
                'title' => 'Dibuat oleh',
                'label' => 'Dibuat oleh:',
                'subtitle' => 'Group Leader',
                'role' => 'Group Leader',
                'name' => $groupLeader,
            ],
            // Backwards compatibility for existing UI references:
            'diketahui' => [
                'title' => 'Diketahui',
                'label' => 'Disetujui oleh:',
                'role' => 'Factory Manager',
                'name' => $manager,
            ],
            'group_spv' => [
                'title' => 'Group SPV',
                'label' => 'Dicheck oleh:',
                'role' => 'Supervisor',
                'name' => $supervisor,
            ],
            'group_leader' => [
                'title' => 'Group Leader',
                'label' => 'Dibuat oleh:',
                'role' => 'Group Leader',
                'name' => $groupLeader,
            ],
            'options' => [
                'users' => $allCreatorsList,
                'supervisors' => $supervisorsList,
                'managers' => $managersList,
            ],
            'available_creators' => $allCreatorsList,
            'available_supervisors' => $supervisorsList,
            'available_managers' => $managersList,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'header' => $header,
                'monthly_grid' => $monthlyGrid,
                'type_breakdown' => [
                    'rows' => $typeRows,
                    'total' => $typeTotalRow,
                ],
                'problems' => [
                    'mesin' => $problemsMesin,
                    'kualitas' => $problemsKualitas,
                    'tool' => $problemsTool,
                ],
                'signatures' => $signatures,
            ],
        ]);
    }

    /**
     * Helper to format array of days into range string (e.g. 2~4, 22~29)
     */
    private function formatDayRanges(array $days): string
    {
        if (empty($days)) {
            return '-';
        }
        $days = array_values(array_unique(array_filter($days)));
        sort($days);
        if (empty($days)) {
            return '-';
        }

        $ranges = [];
        $start = null;
        $prev = null;

        foreach ($days as $day) {
            if ($start === null) {
                $start = $day;
                $prev = $day;
            } elseif ($day == $prev + 1) {
                $prev = $day;
            } else {
                $ranges[] = ($start == $prev) ? (string) $start : "{$start}~{$prev}";
                $start = $day;
                $prev = $day;
            }
        }
        if ($start !== null) {
            $ranges[] = ($start == $prev) ? (string) $start : "{$start}~{$prev}";
        }

        return implode(', ', $ranges);
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

