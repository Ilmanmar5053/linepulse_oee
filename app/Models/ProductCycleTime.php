<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCycleTime extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'production_line_id',
        'machine_id',
        'ideal_cycle_time',
        'unit',
        'revision',
        'effective_from',
        'effective_to',
        'status',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'ideal_cycle_time' => 'float',
        'effective_from' => 'date:Y-m-d',
        'effective_to' => 'date:Y-m-d',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for active records
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'ACTIVE');
    }

    /**
     * Scope for records effective on a specific date
     */
    public function scopeForDate(Builder $query, string $date, ?int $machineId = null, ?int $lineId = null): Builder
    {
        return $query->where('effective_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', $date);
            })
            ->when($machineId, function ($q) use ($machineId) {
                $q->where(function ($sub) use ($machineId) {
                    $sub->whereNull('machine_id')->orWhere('machine_id', $machineId);
                });
            })
            ->when($lineId, function ($q) use ($lineId) {
                $q->where(function ($sub) use ($lineId) {
                    $sub->whereNull('production_line_id')->orWhere('production_line_id', $lineId);
                });
            })
            ->orderByRaw('CASE WHEN machine_id IS NOT NULL THEN 1 WHEN production_line_id IS NOT NULL THEN 2 ELSE 3 END')
            ->orderBy('effective_from', 'desc')
            ->orderBy('id', 'desc');
    }
}
