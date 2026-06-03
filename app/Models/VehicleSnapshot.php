<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleSnapshot extends Model
{
    protected $table = 'vehicle_snapshots';

    protected $fillable = [
        'vehicle_id',
        'company_name',
        'type',
        'entry_year',
        'mileage',
        'plat_new',
        'status',
        'fuel_type',
        'mtc',
        'isprivate',
        'desc',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'isprivate' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /** Capture a full snapshot of the vehicle's current state. */
    public static function recordFor(Vehicle $vehicle): void
    {
        self::create([
            'vehicle_id' => $vehicle->getKey(),
            'company_name' => $vehicle->company?->name,
            'type' => $vehicle->type,
            'entry_year' => $vehicle->entry_year,
            'mileage' => $vehicle->mileage,
            'plat_new' => $vehicle->plat_new,
            'status' => $vehicle->status,
            'fuel_type' => $vehicle->fuel_type,
            'mtc' => $vehicle->mtc,
            'isprivate' => $vehicle->isprivate,
            'desc' => $vehicle->desc,
            'changed_by' => auth()->id(),
        ]);
    }
}
