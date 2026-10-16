<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRecordItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_record_id',
        'product_id',
        'ideal_cycle_time',
        'target_quantity',
        'good_quantity',
        'reject_quantity',
        'scrap_quantity',
        'total_quantity',
        'remarks',
    ];

    protected $casts = [
        'ideal_cycle_time' => 'float',
        'target_quantity' => 'integer',
        'good_quantity' => 'integer',
        'reject_quantity' => 'integer',
        'scrap_quantity' => 'integer',
        'total_quantity' => 'integer',
    ];

    public function productionRecord(): BelongsTo
    {
        return $this->belongsTo(ProductionRecord::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
