<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyTenant;
use App\Models\Concerns\HasDiffrntHashId;
use App\Support\S3Url;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Vehicle extends Model
{
    use BelongsToCompanyTenant, HasDiffrntHashId, HasFactory, SoftDeletes;

    protected $table = 'vehicles';

    protected static function booted(): void
    {
        static::created(fn (self $vehicle) => VehicleSnapshot::recordFor($vehicle));
        static::updated(fn (self $vehicle) => VehicleSnapshot::recordFor($vehicle));
    }

    protected $fillable = [
        'company_id',
        'fuel_type_id',
        'vehicle_type_id',
        'vehicle_brand_id',
        'type',
        'entry_year',
        'mileage',
        'plat_new',
        'status',
        'fuel_type',
        'mtc',
        'desc',
        'isprivate',
        'machine_number',
        'vin_number',
        'bpkb_image',
        'stnk_image',
        'kir_image',
        'bpkb_due_date',
        'stnk_due_date',
        'kir_due_date',
        'purchase_price',
        'selling_price',
        'purchase_date',
        'sale_date',
    ];

    protected function casts(): array
    {
        return [
            'isprivate' => 'boolean',
            'bpkb_image' => 'array',
            'stnk_image' => 'array',
            'kir_image' => 'array',
            'bpkb_due_date' => 'date',
            'stnk_due_date' => 'date',
            'kir_due_date' => 'date',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'purchase_date' => 'date',
            'sale_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $vehicle = null): array
    {
        $ignoreId = $vehicle?->getKey();

        return [
            'type' => ['required', 'string', 'max:50'],
            'vehicle_type_id' => ['nullable', 'integer', Rule::exists(VehicleType::class, 'id')],
            'vehicle_brand_id' => ['nullable', 'integer', Rule::exists(VehicleBrand::class, 'id')],
            'entry_year' => ['required', 'string', 'max:5'],
            'mileage' => ['nullable', 'string', 'max:255'],
            'plat_new' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plat_new')->ignore($ignoreId)],
            'status' => ['required', Rule::in(['ACTIVE', 'PASSIVE'])],
            'fuel_type' => ['required', 'string', 'max:255'],
            'fuel_type_id' => ['nullable', 'integer', Rule::exists(FuelType::class, 'id')],
            'mtc' => ['required', Rule::in(['YES', 'NO'])],
            'desc' => ['nullable', 'string'],
            'isprivate' => ['required', 'boolean'],
            'company_id' => ['nullable', 'integer', Rule::exists(Company::class, 'id')],
            'machine_number' => ['nullable', 'string', 'max:100'],
            'vin_number' => ['nullable', 'string', 'max:100'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'purchase_date' => ['nullable', 'date'],
            'sale_date' => ['nullable', 'date'],
            'bpkb_image' => ['nullable', 'array'],
            'stnk_image' => ['nullable', 'array'],
            'kir_image' => ['nullable', 'array'],
            'bpkb_due_date' => ['nullable', 'date'],
            'stnk_due_date' => ['nullable', 'date'],
            'kir_due_date' => ['nullable', 'date'],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'plat_new.unique' => 'Plat baru sudah tersedia.',
        ];
    }

    public static function getByNoPol(string $plat): ?self
    {
        return static::where('plat_new', $plat)->first();
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function fuelType(): BelongsTo
    {
        return $this->belongsTo(FuelType::class);
    }

    public function vehicleType(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class);
    }

    public function vehicleBrand(): BelongsTo
    {
        return $this->belongsTo(VehicleBrand::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(VehicleSnapshot::class)->latest();
    }

    public function insurances(): HasMany
    {
        return $this->hasMany(VehicleInsurance::class)->latest('end_date');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VehiclePhoto::class)->latest('period');
    }

    /** @return array<int, string> */
    public function bpkbImageUrls(): array
    {
        return $this->documentImageUrls($this->bpkb_image);
    }

    /** @return array<int, string> */
    public function stnkImageUrls(): array
    {
        return $this->documentImageUrls($this->stnk_image);
    }

    /** @return array<int, string> */
    public function kirImageUrls(): array
    {
        return $this->documentImageUrls($this->kir_image);
    }

    /**
     * @param  array<int, string>|null  $paths
     * @return array<int, string>
     */
    protected function documentImageUrls(?array $paths): array
    {
        return collect($paths ?? [])
            ->filter()
            ->map(fn (string $path): string => S3Url::temporary($path))
            ->values()
            ->all();
    }
}
