<?php

namespace App\Models;

use App\Enums\SixBigLoss;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DowntimeReason extends Model
{
    use HasFactory;

    protected $fillable = [
        'downtime_category_id',
        'six_big_loss_category',
        'code',
        'name',
        'description',
    ];

    protected $casts = [
        'six_big_loss_category' => SixBigLoss::class,
    ];

    public function downtimeCategory(): BelongsTo
    {
        return $this->belongsTo(DowntimeCategory::class);
    }

    public function downtimes(): HasMany
    {
        return $this->hasMany(Downtime::class);
    }
}
