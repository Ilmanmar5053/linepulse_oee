<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['department_id', 'nik', 'name', 'email', 'phone'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function operator(): HasOne
    {
        return $this->hasOne(Operator::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
