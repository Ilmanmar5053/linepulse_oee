<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Downtime;
use App\Models\Machine;
use App\Models\NgRecord;
use App\Models\OeeRecord;
use App\Models\Product;
use App\Models\ProductionRecord;
use App\Models\ProductionRecordItem;
use App\Models\QualityRecord;
use App\Services\Oee\OeeCalculationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    private OeeCalculationService $oeeService;

    public function __construct(OeeCalculationService $oeeService)
    {
        $this->oeeService = $oeeService;
    }

    /**
     * Real-time Machine Status Grid (TV & Production Floor Display Mode)
     */
    public function realtimeStatus(Request $request): JsonResponse
    {
        $todayStr = Carbon::today()->toDateString();

        // Determine Date Range from Request
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        } elseif ($request->filled('date')) {
            $startDate = Carbon::parse($request->date)->startOfDay();
            $endDate = Carbon::parse($request->date)->endOfDay();
        } else {
            $period = $request->get('period', 'today');
            switch ($period) {
                case 'yesterday':
                    $startDate = Carbon::yesterday()->startOfDay();
                    $endDate = Carbon::yesterday()->endOfDay();
                    break;
                case 'last_7_days':
                case '7days':
                    $startDate = Carbon::now()->subDays(7)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    break;
                case 'last_30_days':
                case '30days':
                    $startDate = Carbon::now()->subDays(30)->startOfDay();
                    $endDate = Carbon::now()->endOfDay();
                    break;
                case 'this_month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    break;
                case 'today':
                default:
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    break;
            }
        }

        $isToday = ($startDate->toDateString() === $todayStr && $endDate->toDateString() === $todayStr);

        // Filter Machines by line_id / machine_id
        $machineQuery = Machine::with(['workCenter.productionLine']);

        if ($request->filled('line_id')) {
            $lineId = $request->line_id;
            $machineQuery->where(function ($q) use ($lineId) {
                $q->whereHas('workCenter', function ($wc) use ($lineId) {
                    $wc->where('production_line_id', $lineId);
                })->orWhere('id', function ($sub) use ($lineId) {
                    $sub->select('machine_id')->from('production_records')->where('production_line_id', $lineId)->limit(1);
                });
            });
        }

        if ($request->filled('machine_id')) {
            if ($request->machine_id === 'all_measuring') {
                $machineQuery->where(function ($q) {
                    $q->where('code', 'LIKE', '%MEASURING%')
                      ->orWhere('name', 'LIKE', '%Measuring%');
                });
            } else {
                $machineQuery->where('id', $request->machine_id);
            }
        }

        $machines = $machineQuery->get();
        $cards = collect();

        foreach ($machines as $machine) {
            $isMeasuring = ($machine->code === 'MC-MEASURING' || str_contains(strtolower($machine->name), 'measuring'));

            $resolveStatus = function ($machineObj, $hasOngoingDt, $isTodayFlag, $hasRec) {
                $dbStatus = strtoupper($machineObj->status ? $machineObj->status->value : 'STOPPED');

                if ($hasOngoingDt) {
                    return ['BREAKDOWN', 'bg-rose-950 text-rose-300 border-rose-800'];
                }

                if ($dbStatus === 'IDLE') {
                    return ['IDLE', 'bg-amber-950 text-amber-300 border-amber-800'];
                }

                if ($dbStatus === 'BREAKDOWN') {
                    return ['BREAKDOWN', 'bg-rose-950 text-rose-300 border-rose-800'];
                }

                if ($dbStatus === 'MAINTENANCE') {
                    return ['MAINTENANCE', 'bg-purple-950 text-purple-300 border-purple-800'];
                }

                if ($dbStatus === 'STOP' || $dbStatus === 'STOPPED' || $dbStatus === 'OFFLINE') {
                    return ['STOP', 'bg-slate-800 text-slate-400 border-slate-700'];
                }

                if ($hasRec) {
                    return ['RUNNING', 'bg-emerald-950 text-emerald-300 border-emerald-800'];
                }

                return ['IDLE', 'bg-amber-950 text-amber-300 border-amber-800'];
            };

            // Query records for this machine
            $recQuery = ProductionRecord::with(['productionLine', 'product', 'shift', 'oeeRecord', 'downtimes', 'items.product'])
                ->where('machine_id', $machine->id)
                ->whereBetween('production_date', [$startDate->toDateString(), $endDate->toDateString()]);

            if ($request->filled('line_id')) {
                $recQuery->where('production_line_id', $request->line_id);
            }

            if ($request->filled('shift_id')) {
                $recQuery->where('shift_id', $request->shift_id);
            }

            $records = $recQuery->orderBy('production_date', 'desc')->orderBy('id', 'desc')->get();

            if ($isMeasuring) {
                if ($records->isEmpty()) {
                    [$calcStatus, $calcBadge] = $resolveStatus($machine, false, $isToday, false);
                    $cards->push([
                        'record_id' => null,
                        'machine_id' => $machine->id,
                        'machine_code' => $machine->code,
                        'machine_name' => $machine->name,
                        'line_name' => $machine->workCenter->productionLine->name ?? 'Unassigned Line',
                        'product_name' => 'Belum Ada Laporan untuk Periode Ini',
                        'status' => $calcStatus,
                        'status_badge_color' => $calcBadge,
                        'target_quantity' => 0,
                        'good_quantity' => 0,
                        'reject_quantity' => 0,
                        'oee' => 0.0,
                        'availability' => 0.0,
                        'performance' => 0.0,
                        'quality' => 0.0,
                        'oee_status' => 'IDLE',
                        'production_date' => $startDate->toDateString(),
                        'has_activity' => false,
                        'last_updated' => Carbon::now()->format('H:i:s'),
                    ]);
                } else {
                    $grouped = $records->groupBy(function ($r) {
                        return ($r->production_line_id ?? '0') . '-' . ($r->product_id ?? '0');
                    });

                    foreach ($grouped as $groupRecords) {
                        $latestRecord = $groupRecords->first();
                        $avgOee = round($groupRecords->avg(fn($r) => $r->oeeRecord->oee ?? 0), 2);
                        $avgAvail = round($groupRecords->avg(fn($r) => $r->oeeRecord->availability ?? 0), 2);
                        $avgPerf = round($groupRecords->avg(fn($r) => $r->oeeRecord->performance ?? 0), 2);
                        $avgQual = round($groupRecords->avg(fn($r) => $r->oeeRecord->quality ?? 0), 2);
                        $totalTarget = $groupRecords->sum('target_quantity');
                        $totalGood = $groupRecords->sum('good_quantity');
                        $totalReject = $groupRecords->sum('reject_quantity');

                        $hasOngoingDowntime = $groupRecords->flatMap->downtimes->whereNull('end_time')->isNotEmpty();
                        [$calcStatus, $calcBadge] = $resolveStatus($machine, $hasOngoingDowntime, $isToday, true);
                        $hasMultiSku = $latestRecord->items && $latestRecord->items->count() > 1;
                        if ($hasMultiSku) {
                            $prodTitle = $latestRecord->items->map(fn($it) => $it->product?->name)->filter()->unique()->values()->join(' & ');
                            $prodSku = $latestRecord->items->map(fn($it) => $it->product?->sku ?? $it->product?->name)->filter()->unique()->values()->join(' & ');
                        } else {
                            $prodTitle = $latestRecord->product ? "{$latestRecord->product->name} - {$latestRecord->product->sku}" : 'N/A';
                            $prodSku = $latestRecord->product->sku ?? 'N/A';
                        }

                        $cards->push([
                            'record_id' => $latestRecord->id,
                            'machine_id' => $machine->id,
                            'machine_code' => $machine->code,
                            'machine_name' => $machine->name . ' (' . ($latestRecord->productionLine->name ?? 'Line') . ' - ' . $prodTitle . ')',
                            'line_name' => $latestRecord->productionLine->name ?? ($machine->workCenter->productionLine->name ?? 'Unassigned Line'),
                            'product_name' => $prodTitle,
                            'product_sku' => $prodSku,
                            'has_multi_sku' => $hasMultiSku,
                            'sku_breakdown' => $latestRecord->items ? $latestRecord->items->map(fn($it) => [
                                'product_id' => $it->product_id,
                                'product_name' => $it->product?->name ?? "Product #{$it->product_id}",
                                'product_sku' => $it->product?->sku ?? "SKU #{$it->product_id}",
                                'total_quantity' => (int) $it->total_quantity,
                                'good_quantity' => (int) $it->good_quantity,
                                'reject_quantity' => (int) $it->reject_quantity,
                            ])->values() : [],
                            'shift_name' => $latestRecord->shift->name ?? 'All Shifts',
                            'status' => $calcStatus,
                            'status_badge_color' => $calcBadge,
                            'target_quantity' => $totalTarget,
                            'good_quantity' => $totalGood,
                            'reject_quantity' => $totalReject,
                            'oee' => $avgOee,
                            'availability' => $avgAvail,
                            'performance' => $avgPerf,
                            'quality' => $avgQual,
                            'oee_status' => $this->oeeService->evaluateStatus($avgOee),
                            'production_date' => $latestRecord->production_date ? Carbon::parse($latestRecord->production_date)->format('Y-m-d') : $startDate->toDateString(),
                            'has_activity' => true,
                            'last_updated' => $latestRecord->updated_at ? $latestRecord->updated_at->format('H:i:s') : Carbon::now()->format('H:i:s'),
                        ]);
                    }
                }
            } else {
                if ($records->isEmpty()) {
                    [$calcStatus, $calcBadge] = $resolveStatus($machine, false, $isToday, false);
                    $cards->push([
                        'record_id' => null,
                        'machine_id' => $machine->id,
                        'machine_code' => $machine->code,
                        'machine_name' => $machine->name,
                        'line_name' => $machine->workCenter->productionLine->name ?? 'Unassigned Line',
                        'product_name' => 'Belum Ada Laporan untuk Periode Ini',
                        'product_sku' => 'N/A',
                        'status' => $calcStatus,
                        'status_badge_color' => $calcBadge,
                        'target_quantity' => 0,
                        'good_quantity' => 0,
                        'reject_quantity' => 0,
                        'oee' => 0.0,
                        'availability' => 0.0,
                        'performance' => 0.0,
                        'quality' => 0.0,
                        'oee_status' => 'IDLE',
                        'production_date' => $startDate->toDateString(),
                        'has_activity' => false,
                        'last_updated' => Carbon::now()->format('H:i:s'),
                    ]);
                } else {
                    $latestRecord = $records->first();
                    $avgOee = round($records->avg(fn($r) => $r->oeeRecord->oee ?? 0), 2);
                    $avgAvail = round($records->avg(fn($r) => $r->oeeRecord->availability ?? 0), 2);
                    $avgPerf = round($records->avg(fn($r) => $r->oeeRecord->performance ?? 0), 2);
                    $avgQual = round($records->avg(fn($r) => $r->oeeRecord->quality ?? 0), 2);
                    $totalTarget = $records->sum('target_quantity');
                    $totalGood = $records->sum('good_quantity');
                    $totalReject = $records->sum('reject_quantity');

                    $hasOngoingDowntime = $records->flatMap->downtimes->whereNull('end_time')->isNotEmpty();
                    [$calcStatus, $calcBadge] = $resolveStatus($machine, $hasOngoingDowntime, $isToday, true);

                    $hasMultiSku = $latestRecord->items && $latestRecord->items->count() > 1;
                    if ($hasMultiSku) {
                        $prodTitle = $latestRecord->items->map(fn($it) => $it->product?->name)->filter()->unique()->values()->join(' & ');
                        $prodSku = $latestRecord->items->map(fn($it) => $it->product?->sku ?? $it->product?->name)->filter()->unique()->values()->join(' & ');
                    } else {
                        $prodTitle = $latestRecord->product ? "{$latestRecord->product->name} - {$latestRecord->product->sku}" : 'Belum Ada Laporan';
                        $prodSku = $latestRecord->product->sku ?? 'N/A';
                    }

                    $cards->push([
                        'record_id' => $latestRecord->id,
                        'machine_id' => $machine->id,
                        'machine_code' => $machine->code,
                        'machine_name' => $machine->name,
                        'line_name' => $latestRecord->productionLine->name ?? ($machine->workCenter->productionLine->name ?? 'Unassigned Line'),
                        'product_name' => $prodTitle,
                        'product_sku' => $prodSku,
                        'status' => $calcStatus,
                        'status_badge_color' => $calcBadge,
                        'target_quantity' => $totalTarget,
                        'good_quantity' => $totalGood,
                        'reject_quantity' => $totalReject,
                        'oee' => $avgOee,
                        'availability' => $avgAvail,
                        'performance' => $avgPerf,
                        'quality' => $avgQual,
                        'oee_status' => $this->oeeService->evaluateStatus($avgOee),
                        'production_date' => $latestRecord->production_date ? Carbon::parse($latestRecord->production_date)->format('Y-m-d') : $startDate->toDateString(),
                        'has_activity' => true,
                        'last_updated' => $latestRecord->updated_at ? $latestRecord->updated_at->format('H:i:s') : Carbon::now()->format('H:i:s'),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'is_today' => $isToday,
            'message' => 'Realtime machine status retrieved',
            'data' => $cards,
        ]);
    }

    /**
     * Index Production Records
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductionRecord::with([
            'productionLine',
            'machine',
            'product',
            'shift',
            'operator.employee',
            'oeeRecord',
            'items.product',
        ])->orderBy('production_date', 'desc')->orderBy('id', 'desc');

        if ($request->filled('line_id')) {
            $query->where('production_line_id', $request->line_id);
        }
        if ($request->filled('machine_id')) {
            $query->where('machine_id', $request->machine_id);
        }
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }
        if ($request->filled('date')) {
            $query->where('production_date', $request->date);
        }

        $records = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $records->items(),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'total' => $records->total(),
            ],
        ]);
    }

    /**
     * Store new Production Record and calculate OEE automatically
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_line_id' => 'required|exists:production_lines,id',
            'machine_id' => 'required|exists:machines,id',
            'product_id' => 'nullable|exists:products,id',
            'shift_id' => 'required|exists:shifts,id',
            'operator_id' => 'nullable|exists:operators,id',
            'production_date' => 'required|date',
            'planned_production_time' => 'required|numeric|min:1',
            'planned_downtime' => 'nullable|numeric|min:0',
            'run_time' => 'nullable|numeric|min:0',
            'downtime' => 'nullable|numeric|min:0',
            'unplanned_downtime' => 'nullable|numeric|min:0',
            'idle_time' => 'nullable|numeric|min:0',
            'ideal_cycle_time' => 'nullable|numeric|min:0.0001',
            'target_quantity' => 'nullable|integer|min:0',
            'total_quantity' => 'nullable|integer|min:0',
            'good_quantity' => 'nullable|integer|min:0',
            'reject_quantity' => 'nullable|integer|min:0',
            'scrap_quantity' => 'nullable|integer|min:0',
            'downtime_logs' => 'nullable|array',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.ideal_cycle_time' => 'nullable|numeric|min:0',
            'items.*.target_quantity' => 'nullable|integer|min:0',
            'items.*.good_quantity' => 'nullable|integer|min:0',
            'items.*.reject_quantity' => 'nullable|integer|min:0',
            'items.*.scrap_quantity' => 'nullable|integer|min:0',
            'items.*.total_quantity' => 'nullable|integer|min:0',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        $hasItems = !empty($validated['items']) && is_array($validated['items']) && count($validated['items']) > 0;

        if ($hasItems) {
            $totalGood = 0;
            $totalReject = 0;
            $totalScrap = 0;
            $totalMeasuring = 0;
            $totalTarget = 0;
            $totalNetSeconds = 0.0;

            foreach ($validated['items'] as $itm) {
                $g = (int)($itm['good_quantity'] ?? 0);
                $r = (int)($itm['reject_quantity'] ?? 0);
                $s = (int)($itm['scrap_quantity'] ?? 0);
                $tot = isset($itm['total_quantity']) && (int)$itm['total_quantity'] > 0 ? (int)$itm['total_quantity'] : ($g + $r + $s);
                $ct = (float)($itm['ideal_cycle_time'] ?? 9.5);
                $tgt = (int)($itm['target_quantity'] ?? 0);

                $totalGood += $g;
                $totalReject += $r;
                $totalScrap += $s;
                $totalMeasuring += $tot;
                $totalTarget += $tgt;
                $totalNetSeconds += ($ct * $tot);
            }

            $validated['good_quantity'] = $totalGood;
            $validated['reject_quantity'] = $totalReject;
            $validated['scrap_quantity'] = $totalScrap;
            $validated['total_quantity'] = $totalMeasuring;

            if ($totalTarget > 0 || !isset($validated['target_quantity']) || $validated['target_quantity'] <= 0) {
                $validated['target_quantity'] = $totalTarget;
            }

            if ($totalMeasuring > 0 && $totalNetSeconds > 0) {
                $validated['ideal_cycle_time'] = round($totalNetSeconds / $totalMeasuring, 4);
            }

            if (empty($validated['product_id'])) {
                $validated['product_id'] = $validated['items'][0]['product_id'];
            }
        } else {
            $validated['good_quantity'] = (int)($validated['good_quantity'] ?? 0);
            $validated['reject_quantity'] = (int)($validated['reject_quantity'] ?? 0);
            $validated['scrap_quantity'] = (int)($validated['scrap_quantity'] ?? 0);
            $validated['total_quantity'] = isset($validated['total_quantity']) ? (int)$validated['total_quantity'] : ($validated['good_quantity'] + $validated['reject_quantity'] + $validated['scrap_quantity']);
            $validated['target_quantity'] = (int)($validated['target_quantity'] ?? 0);
            if (empty($validated['product_id'])) {
                $validated['product_id'] = Product::first()->id ?? 1;
            }
        }

        // Validasi Duplikasi: Dalam 1 Line pada tanggal yang sama tidak boleh menggunakan Shift yang sama
        $existingRecord = ProductionRecord::with(['productionLine', 'product', 'shift', 'machine'])
            ->where('production_line_id', $validated['production_line_id'])
            ->where('shift_id', $validated['shift_id'])
            ->where('production_date', $validated['production_date'])
            ->first();

        if ($existingRecord) {
            $lineName = $existingRecord->productionLine->name ?? ('Line #' . $validated['production_line_id']);
            $productName = $existingRecord->product->name ?? ('Product #' . $validated['product_id']);
            $shiftName = $existingRecord->shift->name ?? ('Shift #' . $validated['shift_id']);
            $machineName = $existingRecord->machine->name ?? ('Machine #' . $existingRecord->machine_id);
            $dateFormatted = Carbon::parse($validated['production_date'])->format('d M Y');

            return response()->json([
                'success' => false,
                'message' => 'Data sudah ditambahkan tidak boleh sama !',
                'error_detail' => "Data Line: {$lineName} dengan {$shiftName} pada Tanggal {$dateFormatted} sudah pernah di-input sebelumnya. Dalam 1 Line tidak boleh menggunakan Shift yang sama pada tanggal yang sama.",
                'parameters' => [
                    'line' => $lineName,
                    'shift' => $shiftName,
                    'tanggal' => $dateFormatted,
                    'produk' => $productName,
                    'mesin' => $machineName,
                    'target_quantity' => $existingRecord->target_quantity,
                    'total_quantity' => $existingRecord->total_quantity,
                    'good_quantity' => $existingRecord->good_quantity,
                    'reject_quantity' => $existingRecord->reject_quantity,
                ]
            ], 422);
        }

        // Planned & Unplanned Downtime fallbacks
        $validated['planned_downtime'] = (float)($validated['planned_downtime'] ?? 0);
        $dtValue = isset($validated['downtime']) ? (float)$validated['downtime'] : (float)($validated['unplanned_downtime'] ?? 0);
        $validated['downtime'] = $dtValue;
        $validated['idle_time'] = (float)($validated['idle_time'] ?? 0);

        // Sum downtime from trouble logs if downtime_logs are provided
        $totalDtFromLogs = 0;
        if ($request->has('downtime_logs') && is_array($request->downtime_logs)) {
            foreach ($request->downtime_logs as $dtLog) {
                if (!empty($dtLog['duration_minutes'])) {
                    $totalDtFromLogs += (float) $dtLog['duration_minutes'];
                }
            }
            if ($totalDtFromLogs > 0 && $validated['downtime'] <= 0) {
                $validated['downtime'] = $totalDtFromLogs;
            }
        }

        // Auto-compute run_time if not explicitly passed
        if (!isset($validated['run_time'])) {
            $validated['run_time'] = max(0, (float)$validated['planned_production_time'] - (float)$validated['planned_downtime'] - (float)$validated['downtime']);
        } else {
            $validated['run_time'] = (float)$validated['run_time'];
        }

        // Resolve effective cycle time based on product and production_date if single product
        if (!$hasItems || empty($validated['ideal_cycle_time'])) {
            $product = Product::find($validated['product_id']);
            if ($product) {
                $effectiveCt = $product->getEffectiveCycleTime(
                    $validated['production_date'],
                    $validated['machine_id'] ?? null,
                    $validated['production_line_id'] ?? null
                );
                if ($effectiveCt && $effectiveCt->ideal_cycle_time > 0) {
                    $validated['ideal_cycle_time'] = (float) $effectiveCt->ideal_cycle_time;
                }
            }
        }

        $availableTime = max(0, (float)$validated['planned_production_time'] - (float)$validated['planned_downtime']);
        $validated['available_production_time'] = $availableTime;
        $validated['production_rate'] = ($validated['run_time'] > 0) ? round(($validated['good_quantity'] / ($validated['run_time'] / 60.0)), 2) : 0;
        $validated['actual_cycle_time'] = ($validated['total_quantity'] > 0) ? round(($validated['run_time'] * 60.0) / $validated['total_quantity'], 4) : ($validated['ideal_cycle_time'] ?? 9.5);
        $validated['status'] = 'COMPLETED';

        $itemsPayload = $validated['items'] ?? [];
        unset($validated['items']);
        unset($validated['unplanned_downtime']);

        $record = ProductionRecord::create($validated);

        // Ensure any pre-existing/orphaned items with this record ID are cleared first
        ProductionRecordItem::where('production_record_id', $record->id)->delete();

        // Save multi-product items
        if ($hasItems) {
            foreach ($itemsPayload as $itm) {
                $g = (int)($itm['good_quantity'] ?? 0);
                $r = (int)($itm['reject_quantity'] ?? 0);
                $s = (int)($itm['scrap_quantity'] ?? 0);
                $tot = isset($itm['total_quantity']) && (int)$itm['total_quantity'] > 0 ? (int)$itm['total_quantity'] : ($g + $r + $s);

                $record->items()->create([
                    'product_id' => $itm['product_id'],
                    'ideal_cycle_time' => (float)($itm['ideal_cycle_time'] ?? 9.5),
                    'target_quantity' => (int)($itm['target_quantity'] ?? 0),
                    'good_quantity' => $g,
                    'reject_quantity' => $r,
                    'scrap_quantity' => $s,
                    'total_quantity' => $tot,
                    'remarks' => $itm['remarks'] ?? null,
                ]);
            }
        } else {
            $record->items()->create([
                'product_id' => $record->product_id,
                'ideal_cycle_time' => $record->ideal_cycle_time ?? 9.5,
                'target_quantity' => $record->target_quantity,
                'good_quantity' => $record->good_quantity,
                'reject_quantity' => $record->reject_quantity,
                'scrap_quantity' => $record->scrap_quantity,
                'total_quantity' => $record->total_quantity,
            ]);
        }

        // Compute & save OEE
        $avail = $this->oeeService->calculateAvailability($record->run_time, $record->available_production_time);
        $freshItems = $record->items()->get();
        if ($freshItems->isNotEmpty()) {
            $perf = $this->oeeService->calculateMultiProductPerformance($freshItems, $record->run_time);
        } else {
            $perf = $this->oeeService->calculatePerformance($record->ideal_cycle_time, $record->total_quantity, $record->run_time);
        }
        $qual = $this->oeeService->calculateQuality($record->good_quantity, $record->total_quantity);
        $oee = $this->oeeService->calculateOee($avail, $perf, $qual);

        // Calculate setup & adjustment minutes vs unplanned equipment failure from downtime logs
        $setupAdjustmentMinutes = 0.0;
        $equipmentFailureMinutes = (float) $record->downtime;
        if ($request->has('downtime_logs') && is_array($request->downtime_logs)) {
            $setupSum = 0.0;
            foreach ($request->downtime_logs as $dt) {
                $pType = strtolower($dt['problem_type'] ?? '');
                $dur = (float) ($dt['duration_minutes'] ?? 0);
                if (str_contains($pType, 'plan') || str_contains($pType, 'setup') || str_contains($pType, 'changeover') || str_contains($pType, 'ganti')) {
                    $setupSum += $dur;
                }
            }
            if ($setupSum > 0) {
                $setupAdjustmentMinutes = $setupSum;
                $equipmentFailureMinutes = max(0, $equipmentFailureMinutes - $setupSum);
            }
        }

        $sixLosses = $this->oeeService->calculateSixBigLosses(
            $record->available_production_time,
            $record->run_time,
            $record->ideal_cycle_time ?? 9.5,
            $record->total_quantity,
            $record->reject_quantity,
            $record->scrap_quantity,
            $equipmentFailureMinutes,
            $setupAdjustmentMinutes,
            $record->idle_time
        );

        OeeRecord::updateOrCreate(
            ['production_record_id' => $record->id],
            [
                'machine_id' => $record->machine_id,
                'production_line_id' => $record->production_line_id,
                'shift_id' => $record->shift_id,
                'record_date' => $record->production_date,
                'availability' => $avail,
                'performance' => $perf,
                'quality' => $qual,
                'oee' => $oee,
                'six_big_losses_summary' => $sixLosses,
            ]
        );

        // Save individual downtime / trouble log reports with full relational linking
        if ($request->has('downtime_logs') && is_array($request->downtime_logs)) {
            $prodDateStr = $record->production_date ? Carbon::parse($record->production_date)->toDateString() : Carbon::today()->toDateString();

            foreach ($request->downtime_logs as $dtLog) {
                if (empty($dtLog['problem_type']) && empty($dtLog['description']) && empty($dtLog['duration_minutes'])) {
                    continue;
                }

                $startTime = null;
                if (!empty($dtLog['start_time'])) {
                    $startTime = str_contains($dtLog['start_time'], '-') ? Carbon::parse($dtLog['start_time']) : Carbon::parse("{$prodDateStr} {$dtLog['start_time']}");
                }

                $endTime = null;
                if (!empty($dtLog['end_time'])) {
                    $endTime = str_contains($dtLog['end_time'], '-') ? Carbon::parse($dtLog['end_time']) : Carbon::parse("{$prodDateStr} {$dtLog['end_time']}");
                }

                $durMins = isset($dtLog['duration_minutes']) && $dtLog['duration_minutes'] !== '' ? (float) $dtLog['duration_minutes'] : ($endTime && $startTime ? $startTime->diffInMinutes($endTime) : 0);

                $probType = $dtLog['problem_type'] ?? 'Problem Mesin Mekanik';
                $reasonId = !empty($dtLog['downtime_reason_id']) ? $dtLog['downtime_reason_id'] : null;

                $statusVal = strtoupper($dtLog['status'] ?? 'CLOSED');
                $downtimeStartTime = $startTime ?: Carbon::parse($prodDateStr)->startOfDay();
                $downtimeEndTime = $endTime;
                if (!$downtimeEndTime && $statusVal !== 'OPEN' && $durMins > 0) {
                    $downtimeEndTime = $downtimeStartTime->copy()->addMinutes((int) ceil($durMins));
                }

                Downtime::create([
                    'production_record_id' => $record->id,
                    'production_line_id' => !empty($dtLog['production_line_id']) ? $dtLog['production_line_id'] : $record->production_line_id,
                    'machine_id' => !empty($dtLog['machine_id']) ? $dtLog['machine_id'] : $record->machine_id,
                    'shift_id' => !empty($dtLog['shift_id']) ? $dtLog['shift_id'] : $record->shift_id,
                    'product_id' => !empty($dtLog['product_id']) ? $dtLog['product_id'] : $record->product_id,
                    'team' => $dtLog['team'] ?? null,
                    'problem_type' => $probType,
                    'start_time' => $downtimeStartTime,
                    'end_time' => $downtimeEndTime,
                    'duration_minutes' => $durMins,
                    'downtime_reason_id' => $reasonId,
                    'description' => $dtLog['description'] ?? null,
                    'cause' => $dtLog['cause'] ?? null,
                    'action_taken' => $dtLog['action_taken'] ?? null,
                    'pic' => $dtLog['pic'] ?? ($dtLog['leader_name'] ?? null),
                    'status' => $statusVal,
                    'is_planned' => filter_var($dtLog['is_planned'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'follow_up_action' => $dtLog['follow_up_action'] ?? null,
                    'due_date' => !empty($dtLog['due_date']) ? $dtLog['due_date'] : null,
                    'pic_followup' => $dtLog['pic_followup'] ?? null,
                    'created_by' => auth()->id() ?? 1,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Production record created & OEE calculated successfully',
            'data' => $record->load(['oeeRecord', 'items.product']),
        ], 201);
    }

    /**
     * Update Production Record and recalculate OEE
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $record = ProductionRecord::with('items')->find($id);
        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Catatan produksi tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'production_line_id' => 'sometimes|required|exists:production_lines,id',
            'machine_id' => 'sometimes|required|exists:machines,id',
            'product_id' => 'nullable|exists:products,id',
            'shift_id' => 'sometimes|required|exists:shifts,id',
            'operator_id' => 'nullable|exists:operators,id',
            'production_date' => 'sometimes|required|date',
            'planned_production_time' => 'sometimes|required|numeric|min:1',
            'planned_downtime' => 'sometimes|required|numeric|min:0',
            'run_time' => 'sometimes|required|numeric|min:0',
            'downtime' => 'nullable|numeric|min:0',
            'idle_time' => 'nullable|numeric|min:0',
            'ideal_cycle_time' => 'nullable|numeric|min:0.0001',
            'target_quantity' => 'nullable|integer|min:0',
            'total_quantity' => 'nullable|integer|min:0',
            'good_quantity' => 'nullable|integer|min:0',
            'reject_quantity' => 'nullable|integer|min:0',
            'scrap_quantity' => 'nullable|integer|min:0',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.ideal_cycle_time' => 'nullable|numeric|min:0',
            'items.*.target_quantity' => 'nullable|integer|min:0',
            'items.*.good_quantity' => 'nullable|integer|min:0',
            'items.*.reject_quantity' => 'nullable|integer|min:0',
            'items.*.scrap_quantity' => 'nullable|integer|min:0',
            'items.*.total_quantity' => 'nullable|integer|min:0',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        $lineId = $validated['production_line_id'] ?? $record->production_line_id;
        $shiftId = $validated['shift_id'] ?? $record->shift_id;
        $prodDate = $validated['production_date'] ?? $record->production_date;

        $existingRecord = ProductionRecord::with(['productionLine', 'product', 'shift', 'machine'])
            ->where('production_line_id', $lineId)
            ->where('shift_id', $shiftId)
            ->where('production_date', $prodDate)
            ->where('id', '!=', $id)
            ->first();

        if ($existingRecord) {
            $lineName = $existingRecord->productionLine->name ?? ('Line #' . $lineId);
            $shiftName = $existingRecord->shift->name ?? ('Shift #' . $shiftId);
            $dateFormatted = Carbon::parse($prodDate)->format('d M Y');

            return response()->json([
                'success' => false,
                'message' => 'Data sudah ditambahkan tidak boleh sama !',
                'error_detail' => "Data Line: {$lineName} dengan {$shiftName} pada Tanggal {$dateFormatted} sudah pernah di-input sebelumnya. Dalam 1 Line tidak boleh menggunakan Shift yang sama pada tanggal yang sama.",
            ], 422);
        }

        $hasItems = isset($validated['items']) && is_array($validated['items']) && count($validated['items']) > 0;

        if ($hasItems) {
            $totalGood = 0;
            $totalReject = 0;
            $totalScrap = 0;
            $totalMeasuring = 0;
            $totalTarget = 0;
            $totalNetSeconds = 0.0;

            foreach ($validated['items'] as $itm) {
                $g = (int)($itm['good_quantity'] ?? 0);
                $r = (int)($itm['reject_quantity'] ?? 0);
                $s = (int)($itm['scrap_quantity'] ?? 0);
                $tot = isset($itm['total_quantity']) && (int)$itm['total_quantity'] > 0 ? (int)$itm['total_quantity'] : ($g + $r + $s);
                $ct = (float)($itm['ideal_cycle_time'] ?? 9.5);
                $tgt = (int)($itm['target_quantity'] ?? 0);

                $totalGood += $g;
                $totalReject += $r;
                $totalScrap += $s;
                $totalMeasuring += $tot;
                $totalTarget += $tgt;
                $totalNetSeconds += ($ct * $tot);
            }

            $validated['good_quantity'] = $totalGood;
            $validated['reject_quantity'] = $totalReject;
            $validated['scrap_quantity'] = $totalScrap;
            $validated['total_quantity'] = $totalMeasuring;

            if ($totalTarget > 0) {
                $validated['target_quantity'] = $totalTarget;
            }

            if ($totalMeasuring > 0 && $totalNetSeconds > 0) {
                $validated['ideal_cycle_time'] = round($totalNetSeconds / $totalMeasuring, 4);
            }

            if (empty($validated['product_id'])) {
                $validated['product_id'] = $validated['items'][0]['product_id'];
            }
        }

        $plannedTime = $validated['planned_production_time'] ?? $record->planned_production_time;
        $plannedDowntime = $validated['planned_downtime'] ?? $record->planned_downtime;
        $runTime = $validated['run_time'] ?? $record->run_time;
        $goodQty = $validated['good_quantity'] ?? $record->good_quantity;
        $totalQty = $validated['total_quantity'] ?? $record->total_quantity;
        $prodDate = $validated['production_date'] ?? $record->production_date;
        $prodId = $validated['product_id'] ?? $record->product_id;
        $machId = $validated['machine_id'] ?? $record->machine_id;
        $lineId = $validated['production_line_id'] ?? $record->production_line_id;

        if (!$hasItems && empty($validated['ideal_cycle_time'])) {
            $product = Product::find($prodId);
            if ($product) {
                $effectiveCt = $product->getEffectiveCycleTime($prodDate, $machId, $lineId);
                if ($effectiveCt && $effectiveCt->ideal_cycle_time > 0) {
                    $validated['ideal_cycle_time'] = (float) $effectiveCt->ideal_cycle_time;
                }
            }
        }
        $idealCycle = $validated['ideal_cycle_time'] ?? $record->ideal_cycle_time ?? 9.5;

        $availableTime = max(0, $plannedTime - $plannedDowntime);
        $validated['available_production_time'] = $availableTime;
        $validated['production_rate'] = ($runTime > 0) ? round(($goodQty / ($runTime / 60.0)), 2) : 0;
        $validated['actual_cycle_time'] = ($totalQty > 0) ? round(($runTime * 60.0) / $totalQty, 4) : $idealCycle;

        $itemsPayload = $validated['items'] ?? null;
        unset($validated['items']);

        $record->update($validated);

        // Always clean up existing items before saving new/updated items
        $record->items()->delete();

        if ($hasItems && is_array($itemsPayload)) {
            foreach ($itemsPayload as $itm) {
                $g = (int)($itm['good_quantity'] ?? 0);
                $r = (int)($itm['reject_quantity'] ?? 0);
                $s = (int)($itm['scrap_quantity'] ?? 0);
                $tot = isset($itm['total_quantity']) && (int)$itm['total_quantity'] > 0 ? (int)$itm['total_quantity'] : ($g + $r + $s);

                $record->items()->create([
                    'product_id' => $itm['product_id'],
                    'ideal_cycle_time' => (float)($itm['ideal_cycle_time'] ?? 9.5),
                    'target_quantity' => (int)($itm['target_quantity'] ?? 0),
                    'good_quantity' => $g,
                    'reject_quantity' => $r,
                    'scrap_quantity' => $s,
                    'total_quantity' => $tot,
                    'remarks' => $itm['remarks'] ?? null,
                ]);
            }
        } else {
            $record->items()->create([
                'product_id' => $record->product_id,
                'ideal_cycle_time' => $record->ideal_cycle_time ?? 9.5,
                'target_quantity' => $record->target_quantity,
                'good_quantity' => $record->good_quantity,
                'reject_quantity' => $record->reject_quantity,
                'scrap_quantity' => $record->scrap_quantity,
                'total_quantity' => $record->total_quantity,
            ]);
        }

        // Recalculate OEE
        $avail = $this->oeeService->calculateAvailability($record->run_time, $record->available_production_time);
        $freshItems = $record->items()->get();
        if ($freshItems->isNotEmpty()) {
            $perf = $this->oeeService->calculateMultiProductPerformance($freshItems, $record->run_time);
        } else {
            $perf = $this->oeeService->calculatePerformance($record->ideal_cycle_time, $record->total_quantity, $record->run_time);
        }
        $qual = $this->oeeService->calculateQuality($record->good_quantity, $record->total_quantity);
        $oee = $this->oeeService->calculateOee($avail, $perf, $qual);

        // Calculate setup & adjustment minutes vs unplanned equipment failure from downtime logs
        $setupAdjustmentMinutes = 0.0;
        $equipmentFailureMinutes = (float) $record->downtime;
        if ($request->has('downtime_logs') && is_array($request->downtime_logs)) {
            $setupSum = 0.0;
            foreach ($request->downtime_logs as $dt) {
                $pType = strtolower($dt['problem_type'] ?? '');
                $dur = (float) ($dt['duration_minutes'] ?? 0);
                if (str_contains($pType, 'plan') || str_contains($pType, 'setup') || str_contains($pType, 'changeover') || str_contains($pType, 'ganti')) {
                    $setupSum += $dur;
                }
            }
            if ($setupSum > 0) {
                $setupAdjustmentMinutes = $setupSum;
                $equipmentFailureMinutes = max(0, $equipmentFailureMinutes - $setupSum);
            }
        }

        $sixLosses = $this->oeeService->calculateSixBigLosses(
            $record->available_production_time,
            $record->run_time,
            $record->ideal_cycle_time ?? 9.5,
            $record->total_quantity,
            $record->reject_quantity,
            $record->scrap_quantity,
            $equipmentFailureMinutes,
            $setupAdjustmentMinutes,
            $record->idle_time
        );

        OeeRecord::updateOrCreate(
            ['production_record_id' => $record->id],
            [
                'machine_id' => $record->machine_id,
                'production_line_id' => $record->production_line_id,
                'shift_id' => $record->shift_id,
                'record_date' => $record->production_date,
                'availability' => $avail,
                'performance' => $perf,
                'quality' => $qual,
                'oee' => $oee,
                'six_big_losses_summary' => $sixLosses,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Laporan produksi berhasil diperbarui & OEE dihitung ulang!',
            'data' => $record->load(['oeeRecord', 'items.product']),
        ]);
    }

    /**
     * Delete Production Record and associated OEE / Downtime logs
     */
    public function destroy(int $id): JsonResponse
    {
        $record = ProductionRecord::find($id);
        if (!$record) {
            return response()->json(['success' => false, 'message' => 'Catatan produksi tidak ditemukan'], 404);
        }

        ProductionRecordItem::where('production_record_id', $id)->delete();
        QualityRecord::where('production_record_id', $id)->delete();
        NgRecord::where('production_record_id', $id)->delete();
        OeeRecord::where('production_record_id', $id)->delete();
        Downtime::where('production_record_id', $id)->delete();
        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catatan produksi berhasil dihapus!',
        ]);
    }
}
