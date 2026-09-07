<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyProductionSummary;
use App\Models\DefectReason;
use App\Models\Downtime;
use App\Models\DowntimeReason;
use App\Models\Machine;
use App\Models\NgRecord;
use App\Models\NgRecordItem;
use App\Models\OeeRecord;
use App\Models\Product;
use App\Models\ProductionLine;
use App\Models\ProductionRecord;
use App\Models\QualityRecord;
use App\Models\Shift;
use App\Models\ShiftSummary;
use App\Models\SystemSetting;
use App\Services\Oee\OeeCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private OeeCalculationService $oeeService;

    public function __construct(OeeCalculationService $oeeService)
    {
        $this->oeeService = $oeeService;
    }

    private static array $knownTableColumns = [
        'production_records' => ['production_line_id' => true, 'machine_id' => true, 'shift_id' => true],
        'oee_records' => ['production_line_id' => true, 'machine_id' => true, 'shift_id' => true],
        'downtimes' => ['production_line_id' => true, 'machine_id' => true, 'shift_id' => true],
        'ng_records' => ['production_line_id' => true, 'machine_id' => true, 'shift_id' => true],
        'quality_records' => ['production_line_id' => true, 'machine_id' => true, 'shift_id' => true],
        'daily_production_summaries' => ['production_line_id' => true, 'machine_id' => true, 'shift_id' => false],
        'shift_summaries' => ['production_line_id' => true, 'machine_id' => true, 'shift_id' => true],
        'machines' => ['production_line_id' => false, 'machine_id' => false, 'shift_id' => false],
    ];

    /**
     * Helper to apply global filter (Plant, Line, Machine, Shift, Date Range)
     */
    private function applyFilters($query, Request $request, string $dateColumn = 'record_date')
    {
        $model = $query->getModel();
        $table = $model->getTable();
        
        $tableCols = self::$knownTableColumns[$table] ?? [
            'production_line_id' => true,
            'machine_id' => true,
            'shift_id' => true,
        ];
        $hasLineCol = $tableCols['production_line_id'] ?? false;
        $hasMachineCol = $tableCols['machine_id'] ?? false;
        $hasShiftCol = $tableCols['shift_id'] ?? false;

        if ($request->filled('plant_id')) {
            if ($hasLineCol) {
                $query->whereHas('productionLine.area', function ($q) use ($request) {
                    $q->where('plant_id', $request->plant_id);
                });
            }
        }

        $lineId = $request->input('line_id') ?: $request->input('production_line_id');
        if ($lineId) {
            if ($hasLineCol) {
                $query->where('production_line_id', $lineId);
            } elseif (method_exists($model, 'productionRecord')) {
                $query->whereHas('productionRecord', function ($q) use ($lineId) {
                    $q->where('production_line_id', $lineId);
                });
            }
        }

        if ($request->filled('machine_id')) {
            if ($request->machine_id === 'all_measuring') {
                if ($hasMachineCol) {
                    $query->whereHas('machine', function ($q) {
                        $q->where('code', 'LIKE', '%MEASURING%')
                          ->orWhere('name', 'LIKE', '%Measuring%');
                    });
                } elseif (method_exists($model, 'productionRecord')) {
                    $query->whereHas('productionRecord.machine', function ($q) {
                        $q->where('code', 'LIKE', '%MEASURING%')
                          ->orWhere('name', 'LIKE', '%Measuring%');
                    });
                }
            } else {
                if ($hasMachineCol) {
                    $query->where('machine_id', $request->machine_id);
                }
            }
        }

        if ($request->filled('shift_id')) {
            if ($hasShiftCol) {
                $query->where('shift_id', $request->shift_id);
            } elseif (method_exists($model, 'productionRecord')) {
                $query->whereHas('productionRecord', function ($q) use ($request) {
                    $q->where('shift_id', $request->shift_id);
                });
            }
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $singleDate = $request->input('date');

        if ($singleDate) {
            $query->whereDate($dateColumn, $singleDate);
        } elseif ($startDate && $endDate) {
            $query->whereBetween($dateColumn, [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } elseif ($request->filled('period')) {
            $today = Carbon::now()->toDateString();
            match ($request->period) {
                'today' => $query->whereDate($dateColumn, $today),
                'yesterday' => $query->whereDate($dateColumn, Carbon::yesterday()->toDateString()),
                '7days' => $query->whereBetween($dateColumn, [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()]),
                '30days' => $query->whereBetween($dateColumn, [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()]),
                'this_month' => $query->whereBetween($dateColumn, [Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfMonth()->endOfDay()]),
                'last_month' => $query->whereBetween($dateColumn, [Carbon::now()->subMonth()->startOfMonth()->startOfDay(), Carbon::now()->subMonth()->endOfMonth()->endOfDay()]),
                default => null,
            };
        } else {
            // Default 30 days
            $query->whereBetween($dateColumn, [
                Carbon::now()->subDays(30)->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        }

        return $query;
    }

    /**
     * Main OEE KPI Cards Data
     */
    public function oeeKpi(Request $request): JsonResponse
    {
        $query = OeeRecord::with(['productionRecord']);
        $this->applyFilters($query, $request, 'record_date');

        $records = $query->get();

        if ($records->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'oee' => 0.0,
                    'availability' => 0.0,
                    'performance' => 0.0,
                    'quality' => 0.0,
                    'target_oee' => (float) SystemSetting::get('target_oee', 85.0),
                    'achievement' => 0.0,
                    'status' => 'CRITICAL',
                    'total_target_qty' => 0,
                    'total_actual_qty' => 0,
                    'total_good_qty' => 0,
                    'total_reject_qty' => 0,
                    'total_downtime_minutes' => 0,
                ],
            ]);
        }

        $avgAvailability = round($records->avg('availability'), 2);
        $avgPerformance = round($records->avg('performance'), 2);
        $avgQuality = round($records->avg('quality'), 2);
        $avgOee = round($this->oeeService->calculateOee($avgAvailability, $avgPerformance, $avgQuality), 2);

        $targetOee = (float) SystemSetting::get('target_oee', 85.0);
        $achievement = ($targetOee > 0) ? round(($avgOee / $targetOee) * 100, 2) : 0.0;
        $status = $this->oeeService->evaluateStatus($avgOee);

        $prodRecordsQuery = ProductionRecord::query();
        $this->applyFilters($prodRecordsQuery, $request, 'production_date');

        $totalTarget = $prodRecordsQuery->sum('target_quantity');
        $totalActual = $prodRecordsQuery->sum('total_quantity');
        $totalGood = $prodRecordsQuery->sum('good_quantity');
        $totalReject = $prodRecordsQuery->sum('reject_quantity');
        $totalDowntime = $prodRecordsQuery->sum('downtime');

        return response()->json([
            'success' => true,
            'data' => [
                'oee' => $avgOee,
                'availability' => $avgAvailability,
                'performance' => $avgPerformance,
                'quality' => $avgQuality,
                'target_oee' => $targetOee,
                'achievement' => $achievement,
                'status' => $status,
                'total_target_qty' => (int) $totalTarget,
                'total_actual_qty' => (int) $totalActual,
                'total_good_qty' => (int) $totalGood,
                'total_reject_qty' => (int) $totalReject,
                'total_downtime_minutes' => (int) $totalDowntime,
            ],
        ]);
    }

    /**
     * OEE Trend Line Chart Data
     */
    public function oeeTrend(Request $request): JsonResponse
    {
        $query = OeeRecord::select(
            'record_date',
            DB::raw('AVG(availability) as avg_availability'),
            DB::raw('AVG(performance) as avg_performance'),
            DB::raw('AVG(quality) as avg_quality')
        )->groupBy('record_date')->orderBy('record_date', 'asc');

        $this->applyFilters($query, $request, 'record_date');

        $trends = $query->get()->map(function ($row) {
            $avail = round($row->avg_availability, 2);
            $perf = round($row->avg_performance, 2);
            $qual = round($row->avg_quality, 2);
            $oee = round(($avail / 100.0) * ($perf / 100.0) * ($qual / 100.0) * 100.0, 2);

            return [
                'date' => Carbon::parse($row->record_date)->format('d M'),
                'raw_date' => $row->record_date,
                'availability' => $avail,
                'performance' => $perf,
                'quality' => $qual,
                'oee' => $oee,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $trends,
        ]);
    }

    /**
     * Six Big Losses Breakdown Chart Data
     */
    public function sixBigLosses(Request $request): JsonResponse
    {
        $query = OeeRecord::query();
        $this->applyFilters($query, $request, 'record_date');

        $records = $query->get();

        $totals = [
            'EQUIPMENT_FAILURE' => 0.0,
            'SETUP_ADJUSTMENT' => 0.0,
            'IDLING_MINOR_STOP' => 0.0,
            'REDUCED_SPEED' => 0.0,
            'PROCESS_DEFECTS' => 0.0,
            'REDUCED_YIELD' => 0.0,
        ];

        foreach ($records as $r) {
            $losses = $r->six_big_losses_summary ?? [];
            foreach ($totals as $key => $val) {
                $totals[$key] += ($losses[$key] ?? 0.0);
            }
        }

        $formatted = [
            ['loss' => 'Equipment Failure', 'minutes' => round($totals['EQUIPMENT_FAILURE'], 1), 'category' => 'Availability Loss'],
            ['loss' => 'Setup & Adjustment', 'minutes' => round($totals['SETUP_ADJUSTMENT'], 1), 'category' => 'Availability Loss'],
            ['loss' => 'Idling & Minor Stops', 'minutes' => round($totals['IDLING_MINOR_STOP'], 1), 'category' => 'Performance Loss'],
            ['loss' => 'Reduced Speed', 'minutes' => round($totals['REDUCED_SPEED'], 1), 'category' => 'Performance Loss'],
            ['loss' => 'Process Defects', 'minutes' => round($totals['PROCESS_DEFECTS'], 1), 'category' => 'Quality Loss'],
            ['loss' => 'Reduced Yield (Scrap)', 'minutes' => round($totals['REDUCED_YIELD'], 1), 'category' => 'Quality Loss'],
        ];

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Downtime Pareto Chart Data
     */
    public function paretoDowntime(Request $request): JsonResponse
    {
        $query = Downtime::with(['downtimeReason'])
            ->select('downtime_reason_id', DB::raw('SUM(duration_minutes) as total_duration'), DB::raw('COUNT(*) as stop_count'))
            ->groupBy('downtime_reason_id')
            ->orderBy('total_duration', 'desc');

        $this->applyFilters($query, $request, 'start_time');

        $items = $query->limit(10)->get();

        $grandTotal = $items->sum('total_duration');
        $cumulative = 0;

        $result = $items->map(function ($row) use ($grandTotal, &$cumulative) {
            $reason = $row->downtimeReason->name ?? 'Unspecified Breakdown';
            $duration = round((float) $row->total_duration, 1);
            $cumulative += $duration;
            $cumPercentage = ($grandTotal > 0) ? round(($cumulative / $grandTotal) * 100, 1) : 0;

            return [
                'reason' => $reason,
                'duration_minutes' => $duration,
                'stop_count' => (int) $row->stop_count,
                'cumulative_percentage' => $cumPercentage,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Pareto Defects Chart Data
     */
    /**
     * Pareto Defects Chart Data & Advanced Quality Analytics
     */
    public function paretoDefects(Request $request): JsonResponse
    {
        // 1. Query NgRecords with global filters applied
        $ngQuery = NgRecord::with(['items', 'defectReason.defectCategory', 'productionRecord']);
        $this->applyFilters($ngQuery, $request, 'production_date');
        $ngRecords = $ngQuery->get();

        $defectAgg = [];
        $sectionAgg = [];
        $componentAgg = [
            'ASSY' => 0,
            'ROD' => 0,
            'CAP' => 0,
            'SUPPLEMENTARY' => 0,
        ];

        foreach ($ngRecords as $ng) {
            if ($ng->items && $ng->items->count() > 0) {
                foreach ($ng->items as $item) {
                    $qty = (int) $item->quantity;
                    if ($qty <= 0) continue;

                    $reason = trim($item->reason ?? '');
                    if (!$reason) $reason = 'Other / Unspecified Defect';

                    $sec = trim($item->section ?? '');
                    if (!$sec) $sec = 'General Area';

                    $comp = strtoupper(trim($item->component_type ?? 'ASSY'));
                    if (isset($componentAgg[$comp])) {
                        $componentAgg[$comp] += $qty;
                    } else {
                        $componentAgg['SUPPLEMENTARY'] += $qty;
                    }

                    if (!isset($defectAgg[$reason])) {
                        $defectAgg[$reason] = [
                            'reason' => $reason,
                            'reject_quantity' => 0,
                            'sections' => [],
                            'component_types' => [],
                        ];
                    }
                    $defectAgg[$reason]['reject_quantity'] += $qty;
                    if (!in_array($sec, $defectAgg[$reason]['sections'])) {
                        $defectAgg[$reason]['sections'][] = $sec;
                    }
                    if (!in_array($comp, $defectAgg[$reason]['component_types'])) {
                        $defectAgg[$reason]['component_types'][] = $comp;
                    }

                    if (!isset($sectionAgg[$sec])) {
                        $sectionAgg[$sec] = 0;
                    }
                    $sectionAgg[$sec] += $qty;
                }
            } else {
                // Fallback to legacy fields in ng_records
                $fields = [
                    ['qty' => $ng->ng_assy, 'rsn' => $ng->assy_reason, 'sec' => $ng->assy_section, 'comp' => 'ASSY'],
                    ['qty' => $ng->ng_rod, 'rsn' => $ng->rod_reason, 'sec' => $ng->rod_section, 'comp' => 'ROD'],
                    ['qty' => $ng->ng_cap, 'rsn' => $ng->cap_reason, 'sec' => $ng->cap_section, 'comp' => 'CAP'],
                ];
                foreach ($fields as $f) {
                    $qty = (int) $f['qty'];
                    if ($qty <= 0) continue;
                    $reason = trim($f['rsn'] ?? '') ?: ($ng->defectReason->name ?? 'Defect ' . $f['comp']);
                    $sec = trim($f['sec'] ?? '') ?: 'Section ' . $f['comp'];
                    $comp = $f['comp'];

                    $componentAgg[$comp] += $qty;

                    if (!isset($defectAgg[$reason])) {
                        $defectAgg[$reason] = [
                            'reason' => $reason,
                            'reject_quantity' => 0,
                            'sections' => [],
                            'component_types' => [],
                        ];
                    }
                    $defectAgg[$reason]['reject_quantity'] += $qty;
                    if (!in_array($sec, $defectAgg[$reason]['sections'])) {
                        $defectAgg[$reason]['sections'][] = $sec;
                    }
                    if (!in_array($comp, $defectAgg[$reason]['component_types'])) {
                        $defectAgg[$reason]['component_types'][] = $comp;
                    }

                    if (!isset($sectionAgg[$sec])) {
                        $sectionAgg[$sec] = 0;
                    }
                    $sectionAgg[$sec] += $qty;
                }

                // Add Non-OEE supplementary parts
                $suppQty = (int) ($ng->ng_bolt + $ng->ng_bush + $ng->ng_nut + $ng->ng_pin);
                if ($suppQty > 0) {
                    $componentAgg['SUPPLEMENTARY'] += $suppQty;
                    $suppReason = 'Supplementary Fasteners / Hardware Defect';
                    if (!isset($defectAgg[$suppReason])) {
                        $defectAgg[$suppReason] = [
                            'reason' => $suppReason,
                            'reject_quantity' => 0,
                            'sections' => ['Bolt / Bush / Nut / Pin'],
                            'component_types' => ['SUPPLEMENTARY'],
                        ];
                    }
                    $defectAgg[$suppReason]['reject_quantity'] += $suppQty;
                }
            }
        }

        // Also merge any QualityRecord if present
        $qQuery = QualityRecord::with(['defectReason'])
            ->select('defect_reason_id', DB::raw('SUM(reject_quantity) as total_rejects'))
            ->whereNotNull('defect_reason_id')
            ->groupBy('defect_reason_id');
        $this->applyFilters($qQuery, $request, 'created_at');
        foreach ($qQuery->get() as $qRow) {
            $rName = $qRow->defectReason->name ?? 'General Defect';
            $rQty = (int) $qRow->total_rejects;
            if ($rQty <= 0) continue;

            if (!isset($defectAgg[$rName])) {
                $defectAgg[$rName] = [
                    'reason' => $rName,
                    'reject_quantity' => 0,
                    'sections' => ['QC Inspection'],
                    'component_types' => ['ASSY'],
                ];
            }
            $defectAgg[$rName]['reject_quantity'] += $rQty;
        }

        // Calculate Production Volume for Quality Rate & PPM
        $prodQuery = ProductionRecord::query();
        $this->applyFilters($prodQuery, $request, 'production_date');
        $totalOutput = (int) $prodQuery->sum('total_quantity');
        $totalGood = (int) $prodQuery->sum('good_quantity');
        $totalProdReject = (int) $prodQuery->sum('reject_quantity');

        // Sort Defect Aggregation Descending
        usort($defectAgg, fn($a, $b) => $b['reject_quantity'] <=> $a['reject_quantity']);
        $grandTotal = array_sum(array_column($defectAgg, 'reject_quantity'));
        if ($grandTotal === 0 && $totalProdReject > 0) {
            $grandTotal = $totalProdReject;
        }

        $cumulative = 0;
        $topDefects = [];
        $cumReject = 0;
        $vitalFewCount = 0;
        $vitalFewVolume = 0;

        // Preload Defect Reasons from database to check for custom department countermeasures
        $defectReasonMap = \App\Models\DefectReason::all()->keyBy(function($item) {
            return strtolower(trim($item->name));
        });

        foreach ($defectAgg as $idx => $row) {
            $qty = (int)$row['reject_quantity'];
            $pct = $grandTotal > 0 ? round(($qty / $grandTotal) * 100, 1) : 0;
            $cumReject += $qty;
            $cumPct = $grandTotal > 0 ? round(($cumReject / $grandTotal) * 100, 1) : 0;

            $isVital = ($cumPct <= 80 || ($idx === 0) || ($cumPct - $pct < 80));

            if ($isVital) {
                $vitalFewCount++;
                $vitalFewVolume += $qty;
            }

            $primarySection = implode(', ', $row['sections']) ?: 'General Area';
            $normalizedReason = strtolower(trim($row['reason']));
            $defectReasonModel = $defectReasonMap->get($normalizedReason);
            $customCountermeasure = $defectReasonModel ? $defectReasonModel->countermeasure : null;
            $defectReasonId = $defectReasonModel ? $defectReasonModel->id : null;

            $topDefects[] = [
                'rank' => $idx + 1,
                'defect_reason_id' => $defectReasonId,
                'reason' => $row['reason'],
                'reject_quantity' => $qty,
                'percentage' => $pct,
                'cumulative_percentage' => $cumPct,
                'is_vital_few' => $isVital,
                'pareto_class' => $isVital ? 'VITAL_FEW' : 'USEFUL_MANY',
                'section' => $primarySection,
                'component_type' => implode(', ', $row['component_types']) ?: 'ASSY',
                'action_recommendation' => !empty($customCountermeasure) ? $customCountermeasure : $this->getDefectCountermeasure($row['reason'], $primarySection),
                'is_custom_countermeasure' => !empty($customCountermeasure),
            ];
        }

        // Limit top defects to 10 for Pareto chart
        $top10Defects = array_slice($topDefects, 0, 10);

        // Section (Bagian NG) Pareto
        arsort($sectionAgg);
        $topSections = [];
        $cumSec = 0;
        $idxSec = 0;
        foreach ($sectionAgg as $secName => $secQty) {
            $cumSec += $secQty;
            $topSections[] = [
                'rank' => ++$idxSec,
                'section' => $secName,
                'reject_quantity' => $secQty,
                'percentage' => $grandTotal > 0 ? round(($secQty / $grandTotal) * 100, 1) : 0,
                'cumulative_percentage' => $grandTotal > 0 ? round(($cumSec / $grandTotal) * 100, 1) : 0,
            ];
            if ($idxSec >= 10) break;
        }

        // Quality Rate and PPM calculations
        $qualityRate = $totalOutput > 0 ? round(($totalGood / $totalOutput) * 100, 2) : 100.0;
        $ppmDefect = $totalOutput > 0 ? round(($grandTotal / $totalOutput) * 1000000) : 0;

        $top1Defect = $topDefects[0] ?? null;
        $top1Section = $topSections[0] ?? null;

        $summary = [
            'total_reject_pcs' => $grandTotal,
            'total_output_pcs' => $totalOutput,
            'total_good_pcs' => $totalGood,
            'quality_rate' => $qualityRate,
            'ppm_defect_rate' => $ppmDefect,
            'total_ng_records' => $ngRecords->count(),
            'top_defect_name' => $top1Defect['reason'] ?? 'No Defects',
            'top_defect_qty' => $top1Defect['reject_quantity'] ?? 0,
            'top_defect_pct' => $top1Defect['percentage'] ?? 0,
            'top_section_name' => $top1Section['section'] ?? 'N/A',
            'top_section_qty' => $top1Section['reject_quantity'] ?? 0,
            'vital_few_count' => $vitalFewCount,
            'vital_few_percentage' => $grandTotal > 0 ? round(($vitalFewVolume / $grandTotal) * 100, 1) : 0,
            'component_distribution' => [
                ['name' => 'ASSY', 'quantity' => $componentAgg['ASSY'], 'percentage' => $grandTotal > 0 ? round(($componentAgg['ASSY'] / $grandTotal) * 100, 1) : 0],
                ['name' => 'ROD', 'quantity' => $componentAgg['ROD'], 'percentage' => $grandTotal > 0 ? round(($componentAgg['ROD'] / $grandTotal) * 100, 1) : 0],
                ['name' => 'CAP', 'quantity' => $componentAgg['CAP'], 'percentage' => $grandTotal > 0 ? round(($componentAgg['CAP'] / $grandTotal) * 100, 1) : 0],
                ['name' => 'SUPPLEMENTARY', 'quantity' => $componentAgg['SUPPLEMENTARY'], 'percentage' => $grandTotal > 0 ? round(($componentAgg['SUPPLEMENTARY'] / $grandTotal) * 100, 1) : 0],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $top10Defects,
            'top_defects' => $topDefects,
            'top_sections' => $topSections,
            'summary' => $summary,
        ]);
    }

    /**
     * Smart Countermeasure Helper for Industrial QC (Kaizen Expert Rules)
     */
    private function getDefectCountermeasure(string $reason, string $category = ''): string
    {
        $r = strtolower($reason . ' ' . $category);
        if (str_contains($r, 'oil hole') || str_contains($r, 'lubang oil') || str_contains($r, 'meleset') || str_contains($r, 'sumbat') || str_contains($r, 'clog')) {
            return 'Periksa debit nozel coolant pada mesin drill oil hole, bersihkan sisa chip gram penyumbat, dan kalibrasi posisi jig pencekam.';
        }
        if (str_contains($r, 'thickness') || str_contains($r, 'tebal') || str_contains($r, 'tolerance') || str_contains($r, 'toleransi')) {
            return 'Lakukan kalibrasi air micrometer bore/thickness gauge, periksa keausan grinding wheel/milling insert, dan sesuaikan parameter tool offset.';
        }
        if (str_contains($r, 'diameter') || str_contains($r, 'out of spec') || str_contains($r, 'dimensi')) {
            return 'Periksa keausan insert/cutting tool CNC, kalibrasi air micrometer bore gauge, dan sesuaikan tool offset.';
        }
        if (str_contains($r, 'porosity') || str_contains($r, 'porositas') || str_contains($r, 'blow hole') || str_contains($r, 'lubang angin') || str_contains($r, 'material') || str_contains($r, 'cor') || str_contains($r, 'casting')) {
            return 'Eskalasi temuan cacat casting ke vendor/foundry terkait proses degassing cairan logam dan pengendalian temperatur cetakan.';
        }
        if (str_contains($r, 'bending') || str_contains($r, 'twist') || str_contains($r, 'warpage') || str_contains($r, 'deform')) {
            return 'Inspeksi kerataan clamping fixture/jig, periksa tekanan hidrolik penjepitan, dan evaluasi residual stress material.';
        }
        if (str_contains($r, 'scratch') || str_contains($r, 'dent') || str_contains($r, 'visual') || str_contains($r, 'permukaan')) {
            return 'Bersihkan clamping seat dari chip gram halus, pasang bantalan urethane pada conveyor & handling tray part.';
        }
        if (str_contains($r, 'serration') || str_contains($r, 'joint') || str_contains($r, 'burr') || str_contains($r, 'flash')) {
            return 'Cek kondisi cutter broaching serration, ganti brush deburring, dan pastikan coolant mengalir lancar.';
        }
        if (str_contains($r, 'thread') || str_contains($r, 'drat') || str_contains($r, 'baut') || str_contains($r, 'bolt')) {
            return 'Ganti tool tap thread, bersihkan blind hole sebelum perakitan, dan verifikasi kalibrasi torque wrench.';
        }
        if (str_contains($r, 'bushing') || str_contains($r, 'press')) {
            return 'Periksa load cell dan stroke limit press machine, pastikan chamfer bushing tidak rusak saat insertion.';
        }
        return 'Lakukan analisis 5-Why problem solving bersama tim Maintenance & QC Line untuk menentukan akar penyebab.';
    }

    /**
     * Machine Performance Ranking Table
     */
    public function machineRanking(Request $request): JsonResponse
    {
        $machines = Machine::with(['workCenter.productionLine'])->get();

        $result = $machines->map(function ($m) use ($request) {
            $q = OeeRecord::where('machine_id', $m->id);
            $this->applyFilters($q, $request, 'record_date');

            $records = $q->get();
            $avail = round($records->avg('availability') ?? 0, 2);
            $perf = round($records->avg('performance') ?? 0, 2);
            $qual = round($records->avg('quality') ?? 0, 2);
            $oee = round($this->oeeService->calculateOee($avail, $perf, $qual), 2);

            $prodQ = ProductionRecord::where('machine_id', $m->id);
            $this->applyFilters($prodQ, $request, 'production_date');

            $target = $prodQ->sum('target_quantity');
            $actual = $prodQ->sum('total_quantity');
            $reject = $prodQ->sum('reject_quantity');
            $downtime = $prodQ->sum('downtime');

            return [
                'id' => $m->id,
                'code' => $m->code,
                'name' => $m->name,
                'line_name' => $m->workCenter->productionLine->name ?? 'N/A',
                'target_quantity' => (int) $target,
                'actual_quantity' => (int) $actual,
                'reject_quantity' => (int) $reject,
                'downtime_minutes' => (int) $downtime,
                'availability' => $avail,
                'performance' => $perf,
                'quality' => $qual,
                'oee' => $oee,
                'status' => $m->status->value ?? 'OFFLINE',
                'oee_status' => $this->oeeService->evaluateStatus($oee),
            ];
        })->sortByDesc('oee')->values();

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Line Performance Ranking Table & Chart
     */
    public function lineRanking(Request $request): JsonResponse
    {
        $lines = ProductionLine::with(['area', 'products'])->get();

        $result = $lines->map(function ($line) use ($request) {
            $q = OeeRecord::where('production_line_id', $line->id);
            $this->applyFilters($q, $request, 'record_date');

            $records = $q->get();
            $avail = round($records->avg('availability') ?? 0, 2);
            $perf = round($records->avg('performance') ?? 0, 2);
            $qual = round($records->avg('quality') ?? 0, 2);
            $oee = round($this->oeeService->calculateOee($avail, $perf, $qual), 2);

            $prodQ = ProductionRecord::where('production_line_id', $line->id);
            $this->applyFilters($prodQ, $request, 'production_date');

            $target = $prodQ->sum('target_quantity');
            $actual = $prodQ->sum('total_quantity');
            $achievement = ($target > 0) ? round(($actual / $target) * 100, 2) : 0.0;

            // 1. Get products actually produced on this line in production_records
            $producedProductIds = ProductionRecord::where('production_line_id', $line->id)
                ->whereNotNull('product_id')
                ->distinct()
                ->pluck('product_id')
                ->toArray();

            $producedNames = !empty($producedProductIds)
                ? Product::whereIn('id', $producedProductIds)->pluck('name')->filter()->values()->all()
                : [];

            // 2. Get master assigned products
            $assignedNames = $line->products ? $line->products->pluck('name')->filter()->values()->all() : [];

            // 3. Dynamic product string: prioritize produced products, fallback to master assigned
            $displayProducts = !empty($producedNames) ? $producedNames : $assignedNames;
            $productSummary = !empty($displayProducts) ? implode(', ', $displayProducts) : 'Belum ada produk';

            return [
                'id' => $line->id,
                'code' => $line->code,
                'name' => $line->name,
                'area_name' => $line->area->name ?? 'N/A',
                'products_produced' => $producedNames,
                'products_assigned' => $assignedNames,
                'product_summary' => $productSummary,
                'target_quantity' => (int) $target,
                'actual_quantity' => (int) $actual,
                'achievement' => $achievement,
                'availability' => $avail,
                'performance' => $perf,
                'quality' => $qual,
                'oee' => $oee,
                'target_oee' => (float) $line->target_oee,
                'oee_status' => $this->oeeService->evaluateStatus($oee),
            ];
        })->sortByDesc('oee')->values();

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Shift Performance Comparison Data
     */
    /**
     * Shift Performance Comparison Data
     */
    public function shiftComparison(Request $request): JsonResponse
    {
        $allShifts = Shift::all();

        $query = OeeRecord::with(['shift', 'productionLine', 'machine', 'productionRecord']);
        $this->applyFilters($query, $request, 'record_date');

        $records = $query->get();

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

                $target = (int) $sRecords->sum(function ($r) {
                    return $r->productionRecord->target_quantity ?? 0;
                });
                $totalActual = (int) $sRecords->sum(function ($r) {
                    return $r->productionRecord->total_quantity ?? 0;
                });
                $good = (int) $sRecords->sum(function ($r) {
                    return $r->productionRecord->good_quantity ?? 0;
                });
                $reject = (int) $sRecords->sum(function ($r) {
                    return $r->productionRecord->reject_quantity ?? 0;
                });

                $plannedMins = (float) $sRecords->sum(function ($r) {
                    return $r->productionRecord->planned_production_time ?? 480;
                });
                $runMins = (float) $sRecords->sum(function ($r) {
                    return $r->productionRecord->run_time ?? 0;
                });
                $downMins = (float) $sRecords->sum(function ($r) {
                    return $r->productionRecord->downtime ?? 0;
                });

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
                'oee_status' => $this->oeeService->evaluateStatus($oee),
                'target_quantity' => $target,
                'actual_quantity' => $totalActual,
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
        unset($item);

        // Daily breakdown per date (sorted descending)
        $dailyGrouped = $records->groupBy(function ($r) {
            return Carbon::parse($r->record_date)->format('Y-m-d');
        })->sortKeysDesc();

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

                    $dTarget = (int) $dsRecords->sum(function ($r) {
                        return $r->productionRecord->target_quantity ?? 0;
                    });
                    $dTotal = (int) $dsRecords->sum(function ($r) {
                        return $r->productionRecord->total_quantity ?? 0;
                    });
                    $dGood = (int) $dsRecords->sum(function ($r) {
                        return $r->productionRecord->good_quantity ?? 0;
                    });
                    $dReject = (int) $dsRecords->sum(function ($r) {
                        return $r->productionRecord->reject_quantity ?? 0;
                    });

                    $dateShifts[] = [
                        'shift_id' => $s->id,
                        'shift_name' => $s->name,
                        'oee' => $doee,
                        'availability' => $da,
                        'performance' => $dp,
                        'quality' => $dq,
                        'target_quantity' => $dTarget,
                        'total_quantity' => $dTotal,
                        'good_quantity' => $dGood,
                        'reject_quantity' => $dReject,
                    ];
                }
            }
            if (!empty($dateShifts)) {
                $dailyBreakdown[] = [
                    'date' => Carbon::parse($dateStr)->format('d M Y'),
                    'raw_date' => $dateStr,
                    'shifts' => $dateShifts
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $shiftData,
            'daily_breakdown' => $dailyBreakdown,
        ]);
    }
}
