<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Database\Factories\FuelTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class FuelType extends Model
{
    /** @use HasFactory<FuelTypeFactory> */
    use Auditable, HasFactory, SoftDeletes;

    public const MERKS = [
        'Pertamina',
        'Vivo',
        'BP',
        'Shell',
    ];

    public const TYPES = [
        'bensin' => 'Bensin',
        'solar' => 'Solar',
    ];

    protected $fillable = [
        'name',
        'type',
        'price',
        'merk',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $fuelType = null, ?string $merk = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(self::class, 'name')
                    ->where(fn ($query) => $query->where('merk', $merk))
                    ->ignore($fuelType)
                    ->withoutTrashed(),
            ],
            'type' => ['required', Rule::in(array_keys(self::TYPES))],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999999.99'],
            'merk' => ['required', Rule::in(self::MERKS)],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'name.unique' => 'Nama fuel type untuk merk tersebut sudah tersedia.',
            'type.in' => 'Tipe harus Bensin atau Solar.',
            'merk.in' => 'Merk harus Pertamina, Vivo, BP, atau Shell.',
        ];
    }

    public static function normalizeMerk(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        foreach (self::MERKS as $merk) {
            if (strtolower($merk) === strtolower(trim($value))) {
                return $merk;
            }
        }

        return trim($value);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
