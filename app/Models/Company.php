<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'company_driver')->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')->withTimestamps();
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
