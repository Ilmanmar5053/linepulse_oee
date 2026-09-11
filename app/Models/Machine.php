<?php

namespace App\Models;

use App\Enums\MachineStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_center_id',
        'machine_type_id',
        'code',
        'name',
        'serial_number',
        'installation_date',
        'status',
        'is_active',
    ];

    protected $casts = [
        'status' => MachineStatus::class,
        'installation_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function workCenter(): BelongsTo
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function machineType(): BelongsTo
    {
        return $this->belongsTo(MachineType::class);
    }

    public function productionRecords(): HasMany
    {
        return $this->hasMany(ProductionRecord::class);
    }

    public function downtimes(): HasMany
    {
        return $this->hasMany(Downtime::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(MachineStatusLog::class);
    }
}
