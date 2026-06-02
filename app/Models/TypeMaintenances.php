<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class TypeMaintenances extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'type_maintenances';

    protected $fillable = [
        'name',
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
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'name.unique' => 'Tipe maintenance sudah tersedia.',
        ];
    }
}
