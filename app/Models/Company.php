<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToCompanyTenant;
use App\Models\Concerns\GeneratesCode;
use App\Models\Concerns\HasDiffrntHashId;
use App\Support\S3Url;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Company extends Model
{
    use Auditable, BelongsToCompanyTenant, GeneratesCode, HasDiffrntHashId, HasFactory, SoftDeletes;

    protected $table = 'companies';

    protected function codePrefix(): string
    {
        return 'CMP';
    }

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
            'name' => ['required', 'string', 'max:255', Rule::unique(self::class, 'name')->ignore($company)->withoutTrashed()],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function imageUrl(): ?string
    {
        return S3Url::temporary($this->image);
    }

    public static function validationMessages(): array
    {
        return [
            'name.unique' => 'Nama perusahaan sudah tersedia.',
        ];
    }

    public function getTenantScopeColumn(): string
    {
        return $this->getKeyName();
    }

    public function getTenantAssignmentColumn(): ?string
    {
        return null;
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
