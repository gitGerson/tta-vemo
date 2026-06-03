<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Company extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'companies';

    protected $fillable = [
        'code',
        'name',
        'email',
        'phone',
        'image',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $company = null): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique(self::class, 'code')->ignore($company)->withoutTrashed()],
            'name' => ['required', 'string', 'max:255', Rule::unique(self::class, 'name')->ignore($company)->withoutTrashed()],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'image' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'code.unique' => 'Kode perusahaan sudah tersedia.',
            'code.max' => 'Kode perusahaan maksimal 20 karakter.',
            'name.unique' => 'Nama perusahaan sudah tersedia.',
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
