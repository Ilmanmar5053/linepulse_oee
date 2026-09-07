<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OeeRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_record_id',
        'machine_id',
        'production_line_id',
        'shift_id',
        'record_date',
        'availability',
        'performance',
        'quality',
        'oee',
        'six_big_losses_summary',
    ];

    protected $casts = [
        'record_date' => 'date',
        'availability' => 'float',
        'performance' => 'float',
        'quality' => 'float',
        'oee' => 'float',
        'six_big_losses_summary' => 'array',
    ];

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
}
