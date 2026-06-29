<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\GeneratesCode;
use App\Models\Concerns\HasDiffrntHashId;
use Database\Factories\VehicleTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class VehicleType extends Model
{
    /** @use HasFactory<VehicleTypeFactory> */
    use Auditable, GeneratesCode, HasDiffrntHashId, HasFactory, SoftDeletes;

    protected function codePrefix(): string
    {
        return 'VT';
    }

    protected $fillable = [
        'code',
        'name',
        'descriptions',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $vehicleType = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique(self::class, 'name')->ignore($vehicleType)->withoutTrashed()],
            'descriptions' => ['nullable', 'string'],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'name.unique' => 'Nama vehicle type sudah tersedia.',
        ];
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
