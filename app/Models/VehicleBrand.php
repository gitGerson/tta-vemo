<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasDiffrntHashId;
use App\Support\S3Url;
use Database\Factories\VehicleBrandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class VehicleBrand extends Model
{
    /** @use HasFactory<VehicleBrandFactory> */
    use Auditable, HasDiffrntHashId, HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'logo',
        'descriptions',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $vehicleBrand = null): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique(self::class, 'code')->ignore($vehicleBrand)->withoutTrashed()],
            'name' => ['required', 'string', 'max:255', Rule::unique(self::class, 'name')->ignore($vehicleBrand)->withoutTrashed()],
            'logo' => ['nullable', 'string', 'max:2048'],
            'descriptions' => ['nullable', 'string'],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'code.unique' => 'Kode vehicle brand sudah tersedia.',
            'name.unique' => 'Nama vehicle brand sudah tersedia.',
        ];
    }

    public function logoUrl(): ?string
    {
        return S3Url::temporary($this->logo);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
