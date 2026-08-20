<?php

namespace App\Models;

use App\Models\Concerns\HasDiffrntHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleInsuranceSnapshot extends Model
{
    use HasDiffrntHashId;

    protected $table = 'vehicle_insurance_snapshots';

    protected $fillable = [
        'vehicle_insurance_id',
        'vehicle_plat',
        'code',
        'insurer_name',
        'policy_number',
        'insurance_type',
        'start_date',
        'end_date',
        'coverage_value',
        'premium',
        'notes',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'coverage_value' => 'decimal:2',
            'premium' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function vehicleInsurance(): BelongsTo
    {
        return $this->belongsTo(VehicleInsurance::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /** Capture a full snapshot of the insurance record's current state. */
    public static function recordFor(VehicleInsurance $insurance): void
    {
        self::create([
            'vehicle_insurance_id' => $insurance->getKey(),
            'vehicle_plat' => $insurance->vehicle?->plat_new,
            'code' => $insurance->code,
            'insurer_name' => $insurance->insurer_name,
            'policy_number' => $insurance->policy_number,
            'insurance_type' => $insurance->insurance_type,
            'start_date' => $insurance->start_date,
            'end_date' => $insurance->end_date,
            'coverage_value' => $insurance->coverage_value,
            'premium' => $insurance->premium,
            'notes' => $insurance->notes,
            'changed_by' => auth()->id(),
        ]);
    }
}
