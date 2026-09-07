<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'team',
        'leader_name',
        'supervisor_name',
        'production_line_id',
    ];

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }
}
