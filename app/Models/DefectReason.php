<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DefectReason extends Model
{
    use HasFactory;

    protected $fillable = ['defect_category_id', 'code', 'name', 'countermeasure'];

    public function defectCategory(): BelongsTo
    {
        return $this->belongsTo(DefectCategory::class);
    }

    public function qualityRecords(): HasMany
    {
        return $this->hasMany(QualityRecord::class);
    }
}
