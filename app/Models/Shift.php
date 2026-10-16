<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'plant_id',
        'code',
        'name',
        'start_time',
        'end_time',
        'gross_duration_minutes',
        'break_duration_minutes',
        'total_loss_seconds',
        'planned_production_minutes',
        'work_hours',
        'break_breakdown',
    ];

    protected $casts = [
        'break_breakdown' => 'array',
        'work_hours' => 'float',
        'gross_duration_minutes' => 'integer',
        'break_duration_minutes' => 'integer',
        'total_loss_seconds' => 'integer',
        'planned_production_minutes' => 'integer',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class);
    }

    /**
     * Compute systematic duration, total loss, effective planned minutes, and decimal work hours.
     */
    public static function computeShiftMetrics(string $startTime, string $endTime, ?array $breakdown = null, ?int $manualBreakMins = null): array
    {
        $start = strtotime("2026-01-01 " . $startTime);
        $end = strtotime("2026-01-01 " . $endTime);
        if ($end <= $start) {
            $end = strtotime("2026-01-02 " . $endTime);
        }
        $grossSeconds = $end - $start;
        $grossMinutes = (int) round($grossSeconds / 60);

        $totalLossSeconds = 0;
        if (!empty($breakdown) && is_array($breakdown)) {
            foreach ($breakdown as $itemMinutes) {
                $totalLossSeconds += ((int) $itemMinutes) * 60;
            }
        } elseif ($manualBreakMins !== null) {
            $totalLossSeconds = $manualBreakMins * 60;
        } else {
            $totalLossSeconds = 4500; // default 75 mins
        }

        $effectiveSeconds = max(0, $grossSeconds - $totalLossSeconds);
        $breakDurationMinutes = (int) round($totalLossSeconds / 60);
        $plannedProductionMinutes = (int) round($effectiveSeconds / 60);
        $workHours = round($effectiveSeconds / 3600, 2);

        return [
            'gross_duration_minutes' => $grossMinutes,
            'break_duration_minutes' => $breakDurationMinutes,
            'total_loss_seconds' => $totalLossSeconds,
            'planned_production_minutes' => $plannedProductionMinutes,
            'work_hours' => $workHours,
        ];
    }
}
