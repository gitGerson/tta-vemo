<?php

namespace App\Models;

use App\Models\Concerns\HasDiffrntHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleSnapshot extends Model
{
    use HasDiffrntHashId;

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
        'machine_number',
        'vin_number',
        'bpkb_image',
        'stnk_image',
        'purchase_price',
        'selling_price',
        'purchase_date',
        'sale_date',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'isprivate' => 'boolean',
            'bpkb_image' => 'array',
            'stnk_image' => 'array',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'purchase_date' => 'date',
            'sale_date' => 'date',
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
            'machine_number' => $vehicle->machine_number,
            'vin_number' => $vehicle->vin_number,
            'bpkb_image' => $vehicle->bpkb_image,
            'stnk_image' => $vehicle->stnk_image,
            'purchase_price' => $vehicle->purchase_price,
            'selling_price' => $vehicle->selling_price,
            'purchase_date' => $vehicle->purchase_date,
            'sale_date' => $vehicle->sale_date,
            'changed_by' => auth()->id(),
        ]);
    }
}
