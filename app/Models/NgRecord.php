<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NgRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_record_id',
        'machine_id',
        'product_id',
        'shift_id',
        'production_date',
        'total_ng_target',
        'ng_assy',
        'assy_section',
        'assy_reason',
        'ng_rod',
        'rod_section',
        'rod_reason',
        'ng_cap',
        'cap_section',
        'cap_reason',
        'items_breakdown',
        'total_ng_oee',
        'ng_bolt',
        'ng_bush',
        'ng_nut',
        'ng_pin',
        'total_ng_non_oee',
        'defect_category_id',
        'defect_reason_id',
        'defect_symptom',
        'action_taken',
        'inspector_name',
        'notes',
    ];

    protected $casts = [
        'production_date' => 'date',
        'total_ng_target' => 'integer',
        'ng_assy' => 'integer',
        'ng_rod' => 'integer',
        'ng_cap' => 'integer',
        'items_breakdown' => 'array',
        'total_ng_oee' => 'integer',
        'ng_bolt' => 'integer',
        'ng_bush' => 'integer',
        'ng_nut' => 'integer',
        'ng_pin' => 'integer',
        'total_ng_non_oee' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(NgRecordItem::class);
    }

    public function productionRecord(): BelongsTo
    {
        return $this->belongsTo(ProductionRecord::class);
    }

    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function defectCategory(): BelongsTo
    {
        return $this->belongsTo(DefectCategory::class);
    }

    public function defectReason(): BelongsTo
    {
        return $this->belongsTo(DefectReason::class);
    }
}
