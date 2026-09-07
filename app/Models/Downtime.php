<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Downtime extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'production_record_id',
        'machine_id',
        'production_line_id',
        'shift_id',
        'product_id',
        'team',
        'problem_type',
        'start_time',
        'end_time',
        'duration_minutes',
        'downtime_category_id',
        'downtime_reason_id',
        'description',
        'action_taken',
        'is_planned',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'float',
        'is_planned' => 'boolean',
    ];

    public function getCalculatedDurationMinutesAttribute(): float
    {
        if ($this->duration_minutes !== null && $this->duration_minutes >= 0) {
            return (float) $this->duration_minutes;
        }

        if (!$this->start_time) {
            return 0.0;
        }

        $endTime = $this->end_time ?? Carbon::now();
        if ($endTime < $this->start_time) {
            $endTime = $endTime->copy()->addDay();
        }

        return round(abs($this->start_time->diffInSeconds($endTime)) / 60.0, 2);
    }

    public function productionRecord(): BelongsTo
    {
        return $this->belongsTo(ProductionRecord::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function downtimeCategory(): BelongsTo
    {
        return $this->belongsTo(DowntimeCategory::class);
    }

    public function downtimeReason(): BelongsTo
    {
        return $this->belongsTo(DowntimeReason::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
