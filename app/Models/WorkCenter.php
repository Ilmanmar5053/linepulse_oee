<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['production_line_id', 'code', 'name'];

    public function productionLine(): BelongsTo
    {
        return $this->belongsTo(ProductionLine::class);
    }

    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class);
    }
}
