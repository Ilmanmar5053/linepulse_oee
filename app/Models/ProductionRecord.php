<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'production_line_id',
        'machine_id',
        'product_id',
        'shift_id',
        'operator_id',
        'production_date',
        'planned_production_time',
        'planned_downtime',
        'available_production_time',
        'run_time',
        'downtime',
        'idle_time',
        'ideal_cycle_time',
        'actual_cycle_time',
        'target_quantity',
        'total_quantity',
        'good_quantity',
        'reject_quantity',
        'scrap_quantity',
        'production_rate',
        'status',
    ];

    protected $casts = [
        'production_date' => 'date',
        'ideal_cycle_time' => 'float',
        'actual_cycle_time' => 'float',
        'production_rate' => 'float',
    ];

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(Operator::class);
    }

    public function downtimes(): HasMany
    {
        return $this->hasMany(Downtime::class);
    }

    public function qualityRecords(): HasMany
    {
        return $this->hasMany(QualityRecord::class);
    }

    public function oeeRecord(): HasOne
    {
        return $this->hasOne(OeeRecord::class);
    }

    public function ngRecord(): HasOne
    {
        return $this->hasOne(NgRecord::class);
    }
}
