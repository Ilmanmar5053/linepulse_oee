<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['plant_id', 'code', 'name'];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
