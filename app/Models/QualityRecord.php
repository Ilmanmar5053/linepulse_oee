<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_record_id',
        'machine_id',
        'product_id',
        'total_quantity',
        'good_quantity',
        'reject_quantity',
        'rework_quantity',
        'scrap_quantity',
        'defect_category_id',
        'defect_reason_id',
        'inspection_time',
        'inspector_id',
        'notes',
    ];

    protected $casts = [
        'inspection_time' => 'datetime',
    ];

    public function getQualityRateAttribute(): float
    {
        if ($this->total_quantity <= 0) {
            return 100.0;
        }
        return round(($this->good_quantity / $this->total_quantity) * 100.0, 2);
    }

    public function productionRecord(): BelongsTo
    {
        return $this->belongsTo(ProductionRecord::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function defectCategory(): BelongsTo
    {
        return $this->belongsTo(DefectCategory::class);
    }

    public function defectReason(): BelongsTo
    {
        return $this->belongsTo(DefectReason::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
