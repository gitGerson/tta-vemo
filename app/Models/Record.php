<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyTenant;
use App\Models\Concerns\HasDiffrntHashId;
use Database\Factories\RecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Record extends Model
{
    /** @use HasFactory<RecordFactory> */
    use BelongsToCompanyTenant, HasDiffrntHashId, HasFactory, SoftDeletes;

    protected $table = 'records';

    protected static function booted(): void
    {
        static::created(fn (self $record) => RecordSnapshot::recordFor($record));
        static::updated(fn (self $record) => RecordSnapshot::recordFor($record));
    }

    protected $fillable = [
        'driver_1',
        'driver_2',
        'vehicle_id',
        'no_pol',
        'destination',
        'company_id',
        'fuel_type_id',
        'start_km',
        'finish_km',
        'totals',
        'bbm',
        'fuel_unit_price',
        'idr',
        'scale',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'fuel_unit_price' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(): array
    {
        return [
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
            'fuel_type_id' => ['required', 'integer', Rule::exists(FuelType::class, 'id')->withoutTrashed()],
            'date' => ['required', 'date'],
            'driver_1' => ['required', 'integer', Rule::exists('drivers', 'id')],
            'driver_2' => ['nullable', 'integer', Rule::exists('drivers', 'id')],
            'no_pol' => ['required', 'string', Rule::exists('vehicles', 'plat_new')],
            'destination' => ['required', 'string', Rule::exists('indonesia_cities', 'name')],
            'start_km' => ['nullable', 'string'],
            'finish_km' => ['required', 'string'],
            'bbm' => ['required', 'numeric', 'min:0'],
            'fuel_unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.FuelType::MAX_PRICE],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'no_pol.exists' => 'Kendaraan tidak ditemukan.',
            'destination.exists' => 'Tujuan tidak ditemukan.',
            'driver_1.exists' => 'Supir 1 tidak ditemukan.',
            'driver_2.exists' => 'Supir 2 tidak ditemukan.',
            'fuel_type_id.exists' => 'Jenis BBM tidak ditemukan.',
            'bbm.numeric' => 'Jumlah BBM harus berupa angka.',
            'fuel_unit_price.required' => 'Harga per Liter wajib diisi.',
            'fuel_unit_price.numeric' => 'Harga per Liter harus berupa angka.',
            'fuel_unit_price.decimal' => 'Harga per Liter maksimal memiliki 2 angka desimal.',
            'fuel_unit_price.min' => 'Harga per Liter tidak boleh negatif.',
            'fuel_unit_price.max' => 'Harga per Liter melebihi batas yang diizinkan.',
        ];
    }

    public static function latestForPlate(string $plat): ?self
    {
        return static::where('no_pol', $plat)->latest()->first();
    }

    /**
     * data-* attributes the edit-modal trigger carries, read back by the modal
     * JS in records/partials/modal.blade.php. Shared by the datatable row
     * action and the Edit button on the detail page.
     *
     * NB: keys must avoid a digit right after a hyphen — `data-driver-1`
     * maps to dataset['driver-1'], not dataset.driver1, so the JS read fails.
     *
     * @return array<string, mixed>
     */
    public function editModalData(): array
    {
        return [
            'update-url' => route('records.update', $this),
            'date' => $this->date?->format('d-m-Y'),
            'company-id' => $this->company_id,
            'driver1' => $this->driver1?->id,
            'driver2' => $this->driver2?->id,
            'no-pol' => $this->no_pol,
            'destination' => $this->destination,
            'start-km' => $this->start_km,
            'finish-km' => $this->finish_km,
            'bbm' => $this->bbm,
            'fuel-type-id' => $this->fuel_type_id,
            'fuel-unit-price' => $this->fuel_unit_price,
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_id');
    }

    public function driver1(): BelongsTo
    {
        // Column stores the driver name, so relate by name rather than id.
        return $this->belongsTo(Driver::class, 'driver_1', 'name')->withTrashed();
    }

    public function driver2(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_2', 'name')->withTrashed();
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(RecordSnapshot::class)->latest();
    }
}
