<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DowntimeCategory extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'is_planned', 'description'];

    protected $casts = [
        'is_planned' => 'boolean',
    ];

    public function downtimeReasons(): HasMany
    {
        return $this->hasMany(DowntimeReason::class);
    }
}
