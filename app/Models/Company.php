<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    use BelongsToCompanyTenant, HasFactory;
    protected $table = 'company';
    protected $guarded = [''];

    public function getTenantScopeColumn(): string
    {
        return $this->getKeyName();
    }

    public function getTenantAssignmentColumn(): ?string
    {
        return null;
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'company_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withTimestamps();
    }

    public function primaryUsers()
    {
        return $this->hasMany(User::class, 'company_id');
    }
}
