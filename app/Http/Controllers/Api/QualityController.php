<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QualityRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QualityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = QualityRecord::with([
            'machine',
            'product',
            'defectCategory',
            'defectReason',
            'inspector',
        ])->orderBy('inspection_time', 'desc');

        if ($request->filled('machine_id')) {
            $query->where('machine_id', $request->machine_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_record_id' => 'required|exists:production_records,id',
            'machine_id' => 'required|exists:machines,id',
            'product_id' => 'required|exists:products,id',
            'total_quantity' => 'required|integer|min:1',
            'good_quantity' => 'required|integer|min:0',
            'reject_quantity' => 'required|integer|min:0',
            'rework_quantity' => 'nullable|integer|min:0',
            'scrap_quantity' => 'nullable|integer|min:0',
            'defect_category_id' => 'nullable|exists:defect_categories,id',
            'defect_reason_id' => 'nullable|exists:defect_reasons,id',
            'inspection_time' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $validated['inspector_id'] = auth()->id() ?? 1;

        $record = QualityRecord::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Quality inspection record created successfully',
            'data' => $record->load(['product', 'defectReason']),
        ], 201);
    }
}
