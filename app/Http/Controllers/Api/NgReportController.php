<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DefectCategory;
use App\Models\DefectReason;
use App\Models\NgRecord;
use App\Models\NgSection;
use App\Models\ProductionRecord;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NgReportController extends Controller
{
    /**
     * Get Work Queue of Daily Production Reports with Defects (Reject > 0 or Scrap > 0)
     */
    public function queue(Request $request): JsonResponse
    {
        $query = ProductionRecord::with([
            'productionLine',
            'machine',
            'product',
            'shift',
            'operator.employee',
            'ngRecord.defectCategory',
            'ngRecord.defectReason',
        ])
        ->where(function ($q) {
            $q->where('reject_quantity', '>', 0)
              ->orWhere('scrap_quantity', '>', 0);
        })
        ->orderBy('production_date', 'asc')
        ->orderBy('production_line_id', 'asc')
        ->orderBy('id', 'asc');

        $startDate = $request->get('start_date') ?: $request->get('date_from');
        $endDate = $request->get('end_date') ?: $request->get('date_to');
        $singleDate = $request->get('date');

        if ($startDate && $endDate) {
            $query->whereBetween('production_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('production_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('production_date', '<=', $endDate);
        } elseif ($singleDate) {
            $query->where('production_date', $singleDate);
        }
        if ($request->filled('line_id')) {
            $query->where('production_line_id', $request->line_id);
        }
        if ($request->filled('machine_id')) {
            if ($request->machine_id === 'all_measuring') {
                $query->whereHas('machine', function ($q) {
                    $q->where('code', 'LIKE', '%MEASURING%')
                      ->orWhere('name', 'LIKE', '%Measuring%');
                });
            } else {
                $query->where('machine_id', $request->machine_id);
            }
        }
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Status Filter: pending vs completed
        $statusFilter = $request->get('status', 'all');
        if ($statusFilter === 'pending') {
            $query->doesntHave('ngRecord');
        } elseif ($statusFilter === 'completed') {
            $query->has('ngRecord');
        }

        $allMatching = $query->get();

        // Calculate Overview Statistics
        $totalReportsWithNg = $allMatching->count();
        $pendingCount = $allMatching->whereNull('ngRecord')->count();
        $completedCount = $allMatching->whereNotNull('ngRecord')->count();
        
        $totalTargetPcs = $allMatching->sum(fn($r) => ($r->reject_quantity ?? 0) + ($r->scrap_quantity ?? 0));
        
        $totalAssy = $allMatching->sum(fn($r) => $r->ngRecord->ng_assy ?? 0);
        $totalRod = $allMatching->sum(fn($r) => $r->ngRecord->ng_rod ?? 0);
        $totalCap = $allMatching->sum(fn($r) => $r->ngRecord->ng_cap ?? 0);
        $totalOeeNg = $totalAssy + $totalRod + $totalCap;

        $totalBolt = $allMatching->sum(fn($r) => $r->ngRecord->ng_bolt ?? 0);
        $totalBush = $allMatching->sum(fn($r) => $r->ngRecord->ng_bush ?? 0);
        $totalNut = $allMatching->sum(fn($r) => $r->ngRecord->ng_nut ?? 0);
        $totalPin = $allMatching->sum(fn($r) => $r->ngRecord->ng_pin ?? 0);
        $totalNonOeeNg = $totalBolt + $totalBush + $totalNut + $totalPin;

        // Transform records into work queue items
        $items = $allMatching->map(function ($record) {
            $totalNgTarget = ($record->reject_quantity ?? 0) + ($record->scrap_quantity ?? 0);
            $ng = $record->ngRecord;

            $isCompleted = !is_null($ng);
            $totalNgOee = $ng ? ($ng->ng_assy + $ng->ng_rod + $ng->ng_cap) : 0;
            $totalNgNonOee = $ng ? ($ng->ng_bolt + $ng->ng_bush + $ng->ng_nut + $ng->ng_pin) : 0;
            $isBalanced = ($totalNgOee === $totalNgTarget);
            $variance = $totalNgOee - $totalNgTarget;

            return [
                'production_record_id' => $record->id,
                'production_date' => $record->production_date ? Carbon::parse($record->production_date)->format('Y-m-d') : '-',
                'formatted_date' => $record->production_date ? Carbon::parse($record->production_date)->format('d M Y') : '-',
                'line_id' => $record->production_line_id,
                'line_code' => $record->productionLine->code ?? 'LINE',
                'line_name' => $record->productionLine->name ?? 'Production Line',
                'machine_id' => $record->machine_id,
                'machine_code' => $record->machine->code ?? '-',
                'machine_name' => $record->machine->name ?? 'Machine',
                'product_id' => $record->product_id,
                'product_sku' => $record->product->sku ?? '-',
                'product_name' => $record->product->name ?? 'Product',
                'shift_id' => $record->shift_id,
                'shift_name' => $record->shift->name ?? 'Shift',
                'shift_code' => $record->shift->code ?? 'S1',
                'operator_name' => $record->operator->employee->name ?? ($record->operator->name ?? 'Operator'),
                'total_output' => $record->total_quantity,
                'good_quantity' => $record->good_quantity,
                'reject_quantity' => $record->reject_quantity,
                'scrap_quantity' => $record->scrap_quantity,
                'total_ng_target' => $totalNgTarget,
                'has_ng_detail' => $isCompleted,
                'status' => $isCompleted ? 'COMPLETED' : 'PENDING',
                'status_label' => $isCompleted ? 'Detail Terisi' : 'Belum Diisi',
                'is_balanced' => $isBalanced,
                'variance' => $variance,
                'ng_detail' => $ng ? [
                    'id' => $ng->id,
                    'ng_assy' => $ng->ng_assy,
                    'assy_section' => $ng->assy_section,
                    'assy_reason' => $ng->assy_reason,
                    'ng_rod' => $ng->ng_rod,
                    'rod_section' => $ng->rod_section,
                    'rod_reason' => $ng->rod_reason,
                    'ng_cap' => $ng->ng_cap,
                    'cap_section' => $ng->cap_section,
                    'cap_reason' => $ng->cap_reason,
                    'items' => $ng->items ?? [],
                    'items_breakdown' => $ng->items_breakdown ?? [],
                    'total_ng_oee' => $totalNgOee,
                    'ng_bolt' => $ng->ng_bolt,
                    'ng_bush' => $ng->ng_bush,
                    'ng_nut' => $ng->ng_nut,
                    'ng_pin' => $ng->ng_pin,
                    'total_ng_non_oee' => $totalNgNonOee,
                    'defect_category_id' => $ng->defect_category_id,
                    'defect_category_name' => $ng->defectCategory->name ?? null,
                    'defect_reason_id' => $ng->defect_reason_id,
                    'defect_reason_name' => $ng->defectReason->name ?? null,
                    'defect_symptom' => $ng->defect_symptom,
                    'action_taken' => $ng->action_taken,
                    'inspector_name' => $ng->inspector_name,
                    'notes' => $ng->notes,
                    'updated_at' => $ng->updated_at ? $ng->updated_at->format('Y-m-d H:i') : null,
                ] : null,
            ];
        });

        // Bank Data Master Bagian NG dari Database
        $ngSectionsBank = NgSection::where('is_active', true)
            ->orderBy('id')
            ->pluck('name')
            ->toArray();

        if (empty($ngSectionsBank)) {
            $ngSectionsBank = [
                'Small End (Pin Bore)',
                'Big End (Crank Bore)',
                'Rod Body (I-Beam)',
                'Cap Body',
                'Joint Face / Serration',
                'Side Face / Thrust Width',
                'Bolt Hole & Thread',
                'Oil Hole (Lubrication)',
                'Bushing Press Fit Area',
                'Assembly Fitment / Rakitan',
            ];
        }

        // Bank Data Master Penyebab NG / Remark dari Database
        $defectReasonsBank = DefectReason::with('defectCategory')
            ->orderBy('defect_category_id')
            ->orderBy('name')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'code' => $r->code,
                    'name' => $r->name,
                    'category' => $r->defectCategory->name ?? 'General',
                ];
            });

        // Bank Data Master OP Mesin dari Database
        $machinesList = \App\Models\Machine::with('workCenter.productionLine')
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $opMachinesBank = $machinesList->map(function ($m) {
            $lineName = $m->workCenter?->productionLine?->name ?? $m->workCenter?->productionLine?->code ?? '';
            $displayName = $m->name ? ($lineName ? "{$m->name} ({$m->code} - {$lineName})" : "{$m->name} ({$m->code})") : $m->code;
            return [
                'id' => $m->id,
                'code' => $m->code,
                'name' => $m->name,
                'line_id' => $m->workCenter?->production_line_id,
                'line_name' => $lineName,
                'display' => $displayName,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'bank_data' => [
                'sections' => $ngSectionsBank,
                'reasons' => $defectReasonsBank,
                'op_machines' => $opMachinesBank,
            ],
            'summary' => [
                'total_reports_with_ng' => $totalReportsWithNg,
                'pending_count' => $pendingCount,
                'completed_count' => $completedCount,
                'total_ng_target_pcs' => $totalTargetPcs,
                'total_ng_oee_pcs' => $totalOeeNg,
                'total_ng_non_oee_pcs' => $totalNonOeeNg,
                'breakdown_oee' => [
                    'assy' => $totalAssy,
                    'rod' => $totalRod,
                    'cap' => $totalCap,
                ],
                'breakdown_non_oee' => [
                    'bolt' => $totalBolt,
                    'bush' => $totalBush,
                    'nut' => $totalNut,
                    'pin' => $totalPin,
                ],
            ],
            'data' => $items,
        ]);
    }

    /**
     * Get specific production record NG detail
     */
    public function show(int $id): JsonResponse
    {
        $record = ProductionRecord::with([
            'productionLine',
            'machine',
            'product',
            'shift',
            'operator.employee',
            'ngRecord.defectCategory',
            'ngRecord.defectReason',
            'ngRecord.items',
        ])->findOrFail($id);

        $totalNgTarget = ($record->reject_quantity ?? 0) + ($record->scrap_quantity ?? 0);
        $ng = $record->ngRecord;

        $ngSectionsBank = NgSection::where('is_active', true)
            ->orderBy('id')
            ->pluck('name')
            ->toArray();

        if (empty($ngSectionsBank)) {
            $ngSectionsBank = [
                'Small End (Pin Bore)',
                'Big End (Crank Bore)',
                'Rod Body (I-Beam)',
                'Cap Body',
                'Joint Face / Serration',
                'Side Face / Thrust Width',
                'Bolt Hole & Thread',
                'Oil Hole (Lubrication)',
                'Bushing Press Fit Area',
                'Assembly Fitment / Rakitan',
            ];
        }

        $defectReasonsBank = DefectReason::with('defectCategory')
            ->orderBy('defect_category_id')
            ->orderBy('name')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'code' => $r->code,
                    'name' => $r->name,
                    'category' => $r->defectCategory->name ?? 'General',
                ];
            });

        $lineId = $record->production_line_id;
        $machinesList = \App\Models\Machine::with('workCenter.productionLine')
            ->where('is_active', true)
            ->when($lineId, function ($q) use ($lineId) {
                $q->whereHas('workCenter', function ($wc) use ($lineId) {
                    $wc->where('production_line_id', $lineId);
                });
            })
            ->orderBy('code')
            ->get();

        $opMachinesBank = $machinesList->map(function ($m) {
            $lineName = $m->workCenter?->productionLine?->name ?? $m->workCenter?->productionLine?->code ?? '';
            $displayName = $m->name ? ($lineName ? "{$m->name} ({$m->code} - {$lineName})" : "{$m->name} ({$m->code})") : $m->code;
            return [
                'id' => $m->id,
                'code' => $m->code,
                'name' => $m->name,
                'line_id' => $m->workCenter?->production_line_id,
                'line_name' => $lineName,
                'display' => $displayName,
            ];
        })->values()->toArray();

        return response()->json([
            'success' => true,
            'bank_data' => [
                'sections' => $ngSectionsBank,
                'reasons' => $defectReasonsBank,
                'op_machines' => $opMachinesBank,
            ],
            'data' => [
                'production_record_id' => $record->id,
                'production_date' => $record->production_date ? Carbon::parse($record->production_date)->format('Y-m-d') : '-',
                'formatted_date' => $record->production_date ? Carbon::parse($record->production_date)->format('d M Y') : '-',
                'line_id' => $record->production_line_id,
                'line_name' => $record->productionLine->name ?? 'Production Line',
                'machine_name' => $record->machine->name ?? 'Machine',
                'machine_code' => $record->machine->code ?? '-',
                'product_name' => $record->product->name ?? 'Product',
                'product_sku' => $record->product->sku ?? '-',
                'shift_name' => $record->shift->name ?? 'Shift',
                'total_output' => $record->total_quantity,
                'good_quantity' => $record->good_quantity,
                'reject_quantity' => $record->reject_quantity,
                'scrap_quantity' => $record->scrap_quantity,
                'total_ng_target' => $totalNgTarget,
                'ng_record' => $ng ? [
                    'id' => $ng->id,
                    'ng_assy' => $ng->ng_assy,
                    'assy_section' => $ng->assy_section,
                    'assy_reason' => $ng->assy_reason,
                    'ng_rod' => $ng->ng_rod,
                    'rod_section' => $ng->rod_section,
                    'rod_reason' => $ng->rod_reason,
                    'ng_cap' => $ng->ng_cap,
                    'cap_section' => $ng->cap_section,
                    'cap_reason' => $ng->cap_reason,
                    'items' => $ng->items ?? [],
                    'items_breakdown' => $ng->items_breakdown ?? [],
                    'total_ng_oee' => $ng->total_ng_oee,
                    'ng_bolt' => $ng->ng_bolt,
                    'ng_bush' => $ng->ng_bush,
                    'ng_nut' => $ng->ng_nut,
                    'ng_pin' => $ng->ng_pin,
                    'total_ng_non_oee' => $ng->total_ng_non_oee,
                    'defect_category_id' => $ng->defect_category_id,
                    'defect_reason_id' => $ng->defect_reason_id,
                    'defect_symptom' => $ng->defect_symptom,
                    'action_taken' => $ng->action_taken,
                    'inspector_name' => $ng->inspector_name,
                    'notes' => $ng->notes,
                ] : null,
            ],
        ]);
    }

    /**
     * Store or Update NG Detail Breakdown with Dynamic Items
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_record_id' => 'required|exists:production_records,id',
            'items' => 'nullable|array',
            'items.*.component_type' => 'required_with:items|in:ASSY,ROD,CAP',
            'items.*.quantity' => 'required_with:items|integer|min:0',
            'items.*.op_machine' => 'nullable|string|max:100',
            'items.*.section' => 'nullable|string|max:100',
            'items.*.reason' => 'nullable|string|max:255',
            'ng_assy' => 'nullable|integer|min:0',
            'assy_section' => 'nullable|string|max:100',
            'assy_reason' => 'nullable|string|max:255',
            'ng_rod' => 'nullable|integer|min:0',
            'rod_section' => 'nullable|string|max:100',
            'rod_reason' => 'nullable|string|max:255',
            'ng_cap' => 'nullable|integer|min:0',
            'cap_section' => 'nullable|string|max:100',
            'cap_reason' => 'nullable|string|max:255',
            'ng_bolt' => 'nullable|integer|min:0',
            'ng_bush' => 'nullable|integer|min:0',
            'ng_nut' => 'nullable|integer|min:0',
            'ng_pin' => 'nullable|integer|min:0',
            'defect_category_id' => 'nullable|exists:defect_categories,id',
            'defect_reason_id' => 'nullable|exists:defect_reasons,id',
            'defect_symptom' => 'nullable|string|max:255',
            'action_taken' => 'nullable|string|max:50',
            'inspector_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $prodRecord = ProductionRecord::findOrFail($validated['production_record_id']);
        $totalNgTarget = ($prodRecord->reject_quantity ?? 0) + ($prodRecord->scrap_quantity ?? 0);

        $items = $validated['items'] ?? [];
        $cleanedItems = [];

        $ngAssy = 0;
        $ngRod = 0;
        $ngCap = 0;

        $firstAssySection = null;
        $firstAssyReason = null;
        $firstRodSection = null;
        $firstRodReason = null;
        $firstCapSection = null;
        $firstCapReason = null;

        if (!empty($items)) {
            foreach ($items as $it) {
                $qty = (int) ($it['quantity'] ?? 0);
                if ($qty <= 0) continue;

                $type = strtoupper(trim($it['component_type'] ?? 'ASSY'));
                $opMachine = trim($it['op_machine'] ?? '');
                $sec = trim($it['section'] ?? '');
                $rsn = trim($it['reason'] ?? '');

                if ($type === 'ASSY') {
                    $ngAssy += $qty;
                    if (!$firstAssySection && $sec) $firstAssySection = $sec;
                    if (!$firstAssyReason && $rsn) $firstAssyReason = $rsn;
                } elseif ($type === 'ROD') {
                    $ngRod += $qty;
                    if (!$firstRodSection && $sec) $firstRodSection = $sec;
                    if (!$firstRodReason && $rsn) $firstRodReason = $rsn;
                } elseif ($type === 'CAP') {
                    $ngCap += $qty;
                    if (!$firstCapSection && $sec) $firstCapSection = $sec;
                    if (!$firstCapReason && $rsn) $firstCapReason = $rsn;
                }

                $cleanedItems[] = [
                    'component_type' => $type,
                    'quantity' => $qty,
                    'op_machine' => $opMachine ?: null,
                    'section' => $sec ?: null,
                    'reason' => $rsn ?: null,
                ];
            }
        } else {
            $ngAssy = (int) ($validated['ng_assy'] ?? 0);
            $ngRod = (int) ($validated['ng_rod'] ?? 0);
            $ngCap = (int) ($validated['ng_cap'] ?? 0);
            $firstAssySection = $validated['assy_section'] ?? null;
            $firstAssyReason = $validated['assy_reason'] ?? null;
            $firstRodSection = $validated['rod_section'] ?? null;
            $firstRodReason = $validated['rod_reason'] ?? null;
            $firstCapSection = $validated['cap_section'] ?? null;
            $firstCapReason = $validated['cap_reason'] ?? null;

            if ($ngAssy > 0) {
                $cleanedItems[] = [
                    'component_type' => 'ASSY',
                    'quantity' => $ngAssy,
                    'section' => $firstAssySection,
                    'reason' => $firstAssyReason,
                ];
            }
            if ($ngRod > 0) {
                $cleanedItems[] = [
                    'component_type' => 'ROD',
                    'quantity' => $ngRod,
                    'section' => $firstRodSection,
                    'reason' => $firstRodReason,
                ];
            }
            if ($ngCap > 0) {
                $cleanedItems[] = [
                    'component_type' => 'CAP',
                    'quantity' => $ngCap,
                    'section' => $firstCapSection,
                    'reason' => $firstCapReason,
                ];
            }
        }

        $totalNgOee = $ngAssy + $ngRod + $ngCap;

        $ngBolt = (int) ($validated['ng_bolt'] ?? 0);
        $ngBush = (int) ($validated['ng_bush'] ?? 0);
        $ngNut = (int) ($validated['ng_nut'] ?? 0);
        $ngPin = (int) ($validated['ng_pin'] ?? 0);
        $totalNgNonOee = $ngBolt + $ngBush + $ngNut + $ngPin;

        $ngRecord = NgRecord::updateOrCreate(
            ['production_record_id' => $prodRecord->id],
            [
                'machine_id' => $prodRecord->machine_id,
                'product_id' => $prodRecord->product_id,
                'shift_id' => $prodRecord->shift_id,
                'production_date' => $prodRecord->production_date,
                'total_ng_target' => $totalNgTarget,
                'ng_assy' => $ngAssy,
                'assy_section' => $firstAssySection,
                'assy_reason' => $firstAssyReason,
                'ng_rod' => $ngRod,
                'rod_section' => $firstRodSection,
                'rod_reason' => $firstRodReason,
                'ng_cap' => $ngCap,
                'cap_section' => $firstCapSection,
                'cap_reason' => $firstCapReason,
                'items_breakdown' => $cleanedItems,
                'total_ng_oee' => $totalNgOee,
                'ng_bolt' => $ngBolt,
                'ng_bush' => $ngBush,
                'ng_nut' => $ngNut,
                'ng_pin' => $ngPin,
                'total_ng_non_oee' => $totalNgNonOee,
                'defect_category_id' => $validated['defect_category_id'] ?? null,
                'defect_reason_id' => $validated['defect_reason_id'] ?? null,
                'defect_symptom' => $validated['defect_symptom'] ?? null,
                'action_taken' => $validated['action_taken'] ?? 'SCRAP',
                'inspector_name' => $validated['inspector_name'] ?? (auth()->user()->name ?? 'QC Inspector'),
                'notes' => $validated['notes'] ?? null,
            ]
        );

        // Sync items into ng_record_items table
        $ngRecord->items()->delete();
        foreach ($cleanedItems as $itemData) {
            $ngRecord->items()->create($itemData);
        }

        $isBalanced = ($totalNgOee === $totalNgTarget);

        return response()->json([
            'success' => true,
            'message' => 'Detail Laporan NG berhasil disimpan.',
            'is_balanced' => $isBalanced,
            'variance' => $totalNgOee - $totalNgTarget,
            'data' => $ngRecord->load(['defectCategory', 'defectReason', 'items']),
        ]);
    }

    /**
     * Delete NG Detail Record
     */
    public function destroy(int $id): JsonResponse
    {
        $record = NgRecord::findOrFail($id);
        $record->delete();

        return response()->json([
            'success' => true,
            'message' => 'Detail data NG berhasil dihapus.',
        ]);
    }
}
