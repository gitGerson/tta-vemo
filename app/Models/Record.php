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
        'idr',
        'scale',
        'date',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(): array
    {
        return [
            'company_id' => ['required', 'integer', Rule::exists('companies', 'id')],
            'fuel_type_id' => ['required', 'integer', Rule::exists('fuel_types', 'id')],
            'date' => ['required', 'date'],
            'driver_1' => ['required', 'integer', Rule::exists('drivers', 'id')],
            'driver_2' => ['nullable', 'integer', Rule::exists('drivers', 'id')],
            'no_pol' => ['required', 'string', Rule::exists('vehicles', 'plat_new')],
            'destination' => ['required', 'string', Rule::exists('indonesia_cities', 'name')],
            'start_km' => ['nullable', 'string'],
            'finish_km' => ['required', 'string'],
            'bbm' => ['required', 'string'],
            'idr' => ['nullable', 'numeric'],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'no_pol.exists' => 'Kendaraan tidak ditemukan.',
            'destination.exists' => 'Tujuan tidak ditemukan.',
            'driver_1.exists' => 'Supir 1 tidak ditemukan.',
            'driver_2.exists' => 'Supir 2 tidak ditemukan.',
        ];
    }

    public static function latestForPlate(string $plat): ?self
    {
        return static::where('no_pol', $plat)->latest()->first();
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
