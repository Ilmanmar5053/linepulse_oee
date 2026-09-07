<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineEvent extends Model
{
    use HasFactory;

    protected $fillable = ['machine_id', 'event_type', 'event_time', 'payload'];

    protected $casts = [
        'event_time' => 'datetime',
        'payload' => 'array',
    ];

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }
}
