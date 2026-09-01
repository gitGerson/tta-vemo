<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasDiffrntHashId;
use Database\Factories\DestinationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Destination extends Model
{
    /** @use HasFactory<DestinationFactory> */
    use Auditable, HasDiffrntHashId, HasFactory, SoftDeletes;

    protected $table = 'destinations';

    protected $fillable = [
        'name',
        'desc',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $destination = null): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique(self::class, 'name')->ignore($destination)->withoutTrashed()],
            'desc' => ['nullable', 'string'],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'name.required' => 'Tujuan wajib diisi.',
            'name.unique' => 'Tujuan sudah tersedia.',
            'name.max' => 'Tujuan maksimal 50 karakter.',
        ];
    }
}
