<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyTenant;
use App\Models\Concerns\HasDiffrntHashId;
use Closure;
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

    public const MAX_TOTAL_PRICE = '999999999999999.99';

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

    /**
     * Pass the record being updated so its stored destination stays valid; see
     * destinationRules().
     */
    public static function validationRules(?self $record = null): array
    {
        return [
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
            'fuel_type_id' => ['required', 'integer', Rule::exists(FuelType::class, 'id')->withoutTrashed()],
            'date' => ['required', 'date'],
            'driver_1' => ['required', 'integer', Rule::exists('drivers', 'id')],
            'driver_2' => ['nullable', 'integer', Rule::exists('drivers', 'id')],
            'no_pol' => ['required', 'string', Rule::exists('vehicles', 'plat_new')],
            'destination' => static::destinationRules($record),
            'start_km' => ['nullable', 'numeric', 'min:0'],
            'finish_km' => ['required', 'numeric', 'min:0'],
            'bbm' => ['required', 'numeric', 'min:0'],
            'fuel_unit_price' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.FuelType::MAX_PRICE],
            'idr' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:'.self::MAX_TOTAL_PRICE],
        ];
    }

    /**
     * Nearly every imported record stores a destination that predates the
     * destinations master ("UNGARAN", "OT PALEMBANG", …). A plain `exists`
     * rule makes those rows unsaveable, since the original value isn't even a
     * selectable option — so an edit would be forced to change the destination.
     * Let the stored value through untouched; any *new* value must still resolve
     * to a destination on record.
     *
     * @return array<int, mixed>
     */
    protected static function destinationRules(?self $record): array
    {
        if ($record?->destination === null) {
            return ['required', 'string', Rule::exists(Destination::class, 'name')->withoutTrashed()];
        }

        $stored = $record->destination;

        return ['required', 'string', function (string $attribute, mixed $value, Closure $fail) use ($stored): void {
            if ($value === $stored || Destination::query()->where('name', $value)->exists()) {
                return;
            }

            $fail(static::validationMessages()['destination.exists']);
        }];
    }

    public static function validationMessages(): array
    {
        return [
            'no_pol.exists' => 'Kendaraan tidak ditemukan.',
            'destination.exists' => 'Tujuan tidak ditemukan.',
            'driver_1.exists' => 'Supir 1 tidak ditemukan.',
            'driver_2.exists' => 'Supir 2 tidak ditemukan.',
            'fuel_type_id.exists' => 'Jenis BBM tidak ditemukan.',
            'start_km.numeric' => 'KM Awal harus berupa angka.',
            'start_km.min' => 'KM Awal tidak boleh negatif.',
            'finish_km.required' => 'KM Akhir wajib diisi.',
            'finish_km.numeric' => 'KM Akhir harus berupa angka.',
            'finish_km.min' => 'KM Akhir tidak boleh negatif.',
            'finish_km.gte' => 'KM Akhir tidak boleh lebih kecil dari KM Awal.',
            'bbm.numeric' => 'Jumlah BBM harus berupa angka.',
            'fuel_unit_price.required' => 'Harga per Liter wajib diisi.',
            'fuel_unit_price.numeric' => 'Harga per Liter harus berupa angka.',
            'fuel_unit_price.decimal' => 'Harga per Liter maksimal memiliki 2 angka desimal.',
            'fuel_unit_price.min' => 'Harga per Liter tidak boleh negatif.',
            'fuel_unit_price.max' => 'Harga per Liter melebihi batas yang diizinkan.',
            'idr.required' => 'Total Harga wajib diisi.',
            'idr.numeric' => 'Total Harga harus berupa angka.',
            'idr.decimal' => 'Total Harga maksimal memiliki 2 angka desimal.',
            'idr.min' => 'Total Harga tidak boleh negatif.',
            'idr.max' => 'Total Harga melebihi batas yang diizinkan.',
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
            // Legacy rows carry no unit price but do carry a stored idr, which the
            // modal falls back to so Total Harga isn't blank. Not submitted.
            'idr' => $this->idr,
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
