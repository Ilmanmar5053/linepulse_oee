<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyProductionSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_line_id',
        'summary_date',
        'total_target_qty',
        'total_actual_qty',
        'total_good_qty',
        'total_reject_qty',
        'availability',
        'performance',
        'quality',
        'oee',
    ];

    protected $casts = [
        'summary_date' => 'date',
        'availability' => 'float',
        'performance' => 'float',
        'quality' => 'float',
        'oee' => 'float',
    ];

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }
}
