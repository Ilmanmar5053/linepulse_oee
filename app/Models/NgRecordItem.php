<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NgRecordItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'ng_record_id',
        'component_type',
        'quantity',
        'op_machine',
        'section',
        'reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function ngRecord(): BelongsTo
    {
        return $this->belongsTo(NgRecord::class);
    }
}
