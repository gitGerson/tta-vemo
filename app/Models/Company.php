<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Company extends Model
{
    use HasFactory;

    protected $table = 'company';

    protected $fillable = [
        'code',
        'name',
        'desc',
    ];

    public static function validationRules(?self $company = null): array
    {
        return [
            'code' => ['required', 'string', 'max:6', Rule::unique('company', 'code')->ignore($company?->getKey())],
            'name' => ['required', 'string', 'max:255'],
            'desc' => ['nullable', 'string'],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'code.unique' => 'Kode perusahaan sudah tersedia.',
            'code.max' => 'Kode perusahaan maksimal 6 karakter.',
        ];
    }
}
