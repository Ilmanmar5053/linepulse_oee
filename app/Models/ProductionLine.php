<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionLine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['area_id', 'plant_id', 'code', 'name', 'target_oee', 'is_active'];

    protected $casts = [
        'target_oee' => 'float',
        'is_active' => 'boolean',
    ];

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function workCenters(): HasMany
    {
        return $this->hasMany(WorkCenter::class);
    }

    public function machines(): HasManyThrough
    {
        return $this->hasManyThrough(Machine::class, WorkCenter::class);
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
