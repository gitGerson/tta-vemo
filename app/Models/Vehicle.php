<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use BelongsToCompanyTenant, HasFactory, SoftDeletes;

    protected $table = 'vehicles';
    protected $guarded = [''];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    static function getByNoPol($plat)
    {
        return static::where('plat_new', $plat)->orWhere('plat_old', $plat)->first();
    }
}
