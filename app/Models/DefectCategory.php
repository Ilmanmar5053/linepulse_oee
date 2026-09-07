<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DefectCategory extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name'];

    public function defectReasons(): HasMany
    {
        return $this->hasMany(DefectReason::class);
    }
}
