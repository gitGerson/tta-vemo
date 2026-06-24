<?php

namespace App\Models;

use App\Models\Concerns\HasDiffrntHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class TypeMaintenances extends Model
{
    use HasDiffrntHashId, HasFactory, SoftDeletes;

    protected $table = 'type_maintenances';

    protected $fillable = [
        'name',
        'jenis',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $typeMaintenance = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('type_maintenances', 'name')->ignore($typeMaintenance?->getKey())],
            'jenis' => ['required', Rule::in(['jasa', 'barang'])],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'name.unique' => 'Tipe maintenance sudah tersedia.',
            'jenis.in' => 'Jenis harus jasa atau barang.',
        ];
    }
}
