<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'production_line_id',
        'product_category_id',
        'sku',
        'name',
        'unit_of_measure',
        'ideal_cycle_time',
    ];

    protected $casts = [
        'ideal_cycle_time' => 'float',
    ];

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function cycleTimes(): HasMany
    {
        return $this->hasMany(ProductCycleTime::class)->orderBy('effective_from', 'desc')->orderBy('id', 'desc');
    }

    public function activeCycleTime(): HasMany
    {
        return $this->hasMany(ProductCycleTime::class)->where('status', 'ACTIVE')->orderBy('effective_from', 'desc');
    }

    public function getEffectiveCycleTime(?string $productionDate = null, ?int $machineId = null, ?int $lineId = null): ?ProductCycleTime
    {
        $date = $productionDate ?: now()->format('Y-m-d');
        
        $resolved = $this->cycleTimes()
            ->forDate($date, $machineId, $lineId ?? $this->production_line_id)
            ->first();

        if ($resolved) {
            return $resolved;
        }

        // Fallback to active cycle time if not found
        $active = $this->cycleTimes()->where('status', 'ACTIVE')->first();
        if ($active) {
            return $active;
        }

        return null;
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class);
    }
}
