<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicles';

    protected static function booted(): void
    {
        static::created(fn (self $vehicle) => VehicleSnapshot::recordFor($vehicle));
        static::updated(fn (self $vehicle) => VehicleSnapshot::recordFor($vehicle));
    }

    protected $fillable = [
        'company_id',
        'type',
        'entry_year',
        'mileage',
        'plat_new',
        'status',
        'fuel_type',
        'mtc',
        'desc',
        'isprivate',
    ];

    protected function casts(): array
    {
        return [
            'isprivate' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $vehicle = null): array
    {
        $ignoreId = $vehicle?->getKey();

        return [
            'type' => ['required', 'string', 'max:50'],
            'entry_year' => ['required', 'string', 'max:5'],
            'mileage' => ['nullable', 'string', 'max:255'],
            'plat_new' => ['required', 'string', 'max:20', Rule::unique('vehicles', 'plat_new')->ignore($ignoreId)],
            'status' => ['required', Rule::in(['ACTIVE', 'PASSIVE'])],
            'fuel_type' => ['required', 'string', 'max:255'],
            'mtc' => ['required', Rule::in(['YES', 'NO'])],
            'desc' => ['nullable', 'string'],
            'isprivate' => ['required', 'boolean'],
            'company_id' => ['nullable', 'integer', Rule::exists(Company::class, 'id')],
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

    public function snapshots(): HasMany
    {
        return $this->hasMany(VehicleSnapshot::class)->latest();
    }
}
