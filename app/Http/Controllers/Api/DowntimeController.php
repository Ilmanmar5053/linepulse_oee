<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Downtime;
use App\Models\ProductionRecord;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DowntimeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Downtime::with([
            'machine',
            'productionLine',
            'shift',
            'product',
            'downtimeCategory',
            'downtimeReason',
            'creator',
        ])->orderBy('start_time', 'desc');

        if ($request->filled('line_id')) {
            $query->where('production_line_id', $request->line_id);
        }
        if ($request->filled('machine_id')) {
            $query->where('machine_id', $request->machine_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }
        if ($request->filled('is_planned')) {
            $query->where('is_planned', filter_var($request->is_planned, FILTER_VALIDATE_BOOLEAN));
        }

        $downtimes = $query->paginate($request->get('per_page', 15));

        // Format dynamic calculated duration for ongoing downtimes
        $formatted = collect($downtimes->items())->map(function ($dt) {
            $item = $dt->toArray();
            $item['calculated_duration_minutes'] = $dt->calculated_duration_minutes;
            $item['is_ongoing'] = is_null($dt->end_time);
            return $item;
        });

        return response()->json([
            'success' => true,
            'data' => $formatted,
            'meta' => [
                'current_page' => $downtimes->currentPage(),
                'last_page' => $downtimes->lastPage(),
                'total' => $downtimes->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'production_record_id' => 'nullable|exists:production_records,id',
            'machine_id' => 'required|exists:machines,id',
            'production_line_id' => 'required|exists:production_lines,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'product_id' => 'nullable|exists:products,id',
            'team' => 'nullable|string',
            'problem_type' => 'nullable|string',
            'downtime_category_id' => 'nullable|exists:downtime_categories,id',
            'downtime_reason_id' => 'nullable|exists:downtime_reasons,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
            'duration_minutes' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'is_planned' => 'boolean',
        ]);

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = !empty($validated['end_time']) ? Carbon::parse($validated['end_time']) : null;
        
        $duration = 0.0;
        if (isset($validated['duration_minutes']) && is_numeric($validated['duration_minutes']) && (float)$validated['duration_minutes'] > 0) {
            $duration = (float) $validated['duration_minutes'];
            if (!$endTime) {
                $endTime = $startTime->copy()->addMinutes((int)ceil($duration));
                $validated['end_time'] = $endTime->format('Y-m-d H:i:s');
            }
        } elseif ($endTime) {
            if ($endTime < $startTime) {
                $endTime->addDay();
                $validated['end_time'] = $endTime->format('Y-m-d H:i:s');
            }
            $duration = round($startTime->diffInSeconds($endTime) / 60.0, 2);
        }

        $validated['duration_minutes'] = $duration;
        $validated['created_by'] = auth()->id() ?? 1;

        $downtime = Downtime::create($validated);

        // Update production record downtime sum
        if ($downtime->production_record_id && $duration > 0) {
            $record = ProductionRecord::find($downtime->production_record_id);
            if ($record) {
                $record->increment('downtime', (int) ceil($duration));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Trouble / Downtime log berhasil disimpan.',
            'data' => $downtime->load(['machine', 'downtimeReason', 'productionLine', 'shift', 'product']),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $downtime = Downtime::find($id);
        if (!$downtime) {
            return response()->json(['success' => false, 'message' => 'Record trouble/downtime tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'production_line_id' => 'nullable|exists:production_lines,id',
            'machine_id' => 'nullable|exists:machines,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'product_id' => 'nullable|exists:products,id',
            'trouble_date' => 'nullable|date',
            'team' => 'nullable|string',
            'problem_type' => 'nullable|string',
            'description' => 'nullable|string',
            'action_taken' => 'nullable|string',
            'duration_minutes' => 'nullable|numeric|min:0',
            'dt_start_time' => 'nullable|string',
            'dt_end_time' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'downtime_reason_id' => 'nullable|exists:downtime_reasons,id',
            'downtime_category_id' => 'nullable|exists:downtime_categories,id',
        ]);

        $baseDate = $validated['trouble_date'] 
            ?? ($downtime->start_time ? $downtime->start_time->format('Y-m-d') : Carbon::now()->format('Y-m-d'));

        if (!empty($validated['dt_start_time'])) {
            $startTimeStr = "{$baseDate} {$validated['dt_start_time']}:00";
            $downtime->start_time = Carbon::parse($startTimeStr);
        } elseif (!empty($validated['start_time'])) {
            $downtime->start_time = Carbon::parse($validated['start_time']);
        }

        if (isset($validated['duration_minutes']) && is_numeric($validated['duration_minutes'])) {
            $dur = max(0, (float) $validated['duration_minutes']);
            $downtime->duration_minutes = $dur;

            if (!empty($validated['dt_end_time'])) {
                $endTimeStr = "{$baseDate} {$validated['dt_end_time']}:00";
                $endTime = Carbon::parse($endTimeStr);
                if ($downtime->start_time && $endTime < $downtime->start_time) {
                    $endTime->addDay();
                }
                $downtime->end_time = $endTime;
            } elseif ($downtime->start_time) {
                $downtime->end_time = $downtime->start_time->copy()->addMinutes($dur);
            }
        } elseif (!empty($validated['dt_end_time']) && $downtime->start_time) {
            $endTimeStr = "{$baseDate} {$validated['dt_end_time']}:00";
            $endTime = Carbon::parse($endTimeStr);
            if ($endTime < $downtime->start_time) {
                $endTime->addDay();
            }
            $downtime->end_time = $endTime;
            $downtime->duration_minutes = round(abs($downtime->start_time->diffInSeconds($endTime)) / 60.0, 2);
        } elseif (!empty($validated['end_time'])) {
            $downtime->end_time = Carbon::parse($validated['end_time']);
            if ($downtime->start_time) {
                $downtime->duration_minutes = round(abs($downtime->start_time->diffInSeconds($downtime->end_time)) / 60.0, 2);
            }
        }

        if (isset($validated['production_line_id'])) $downtime->production_line_id = $validated['production_line_id'];
        if (isset($validated['machine_id'])) $downtime->machine_id = $validated['machine_id'];
        if (isset($validated['shift_id'])) $downtime->shift_id = $validated['shift_id'];
        if (isset($validated['product_id'])) $downtime->product_id = $validated['product_id'];
        if (isset($validated['team'])) $downtime->team = $validated['team'];
        if (isset($validated['problem_type'])) $downtime->problem_type = $validated['problem_type'];
        if (isset($validated['downtime_reason_id'])) {
            $downtime->downtime_reason_id = $validated['downtime_reason_id'];
            $reason = \App\Models\DowntimeReason::find($validated['downtime_reason_id']);
            if ($reason && $reason->downtime_category_id) {
                $downtime->downtime_category_id = $reason->downtime_category_id;
            }
        }
        if (isset($validated['downtime_category_id'])) $downtime->downtime_category_id = $validated['downtime_category_id'];
        if (isset($validated['description'])) $downtime->description = $validated['description'];
        if (isset($validated['action_taken'])) $downtime->action_taken = $validated['action_taken'];

        $downtime->save();

        if ($downtime->production_record_id) {
            $totalDowntimeMins = Downtime::where('production_record_id', $downtime->production_record_id)->sum('duration_minutes');
            ProductionRecord::where('id', $downtime->production_record_id)->update(['downtime' => (int) ceil($totalDowntimeMins)]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Log trouble/downtime berhasil diperbarui!',
            'data' => $downtime,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $downtime = Downtime::find($id);
        if (!$downtime) {
            return response()->json(['success' => false, 'message' => 'Record trouble/downtime tidak ditemukan'], 404);
        }
        $downtime->delete();
        return response()->json(['success' => true, 'message' => 'Log trouble/downtime berhasil dihapus!']);
    }
}
