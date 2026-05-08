<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Record extends Model
{
    use BelongsToCompanyTenant, HasFactory, SoftDeletes;
    protected $table = 'records';
    protected $guarded = [''];

    public function driver1()
    {
        // Column stores driver name, so relate by name rather than id
        return $this->belongsTo(Driver::class, 'driver_1', 'name')->withTrashed();
    }

    public function driver2()
    {
        // Column stores driver name, so relate by name rather than id
        return $this->belongsTo(Driver::class, 'driver_2', 'name')->withTrashed();
    }

    public function vehicle()
    {
        // Migration and controller use vehicle_id
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function destination()
    {
        // Column stores destination text, not id
        return $this->belongsTo(Destination::class, 'destination', 'destination');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function fuelType()
    {
        return $this->belongsTo(FuelType::class, 'fuel_type_id');
    }

    static function checkNoPol($noPol)
    {
        $search = static::where('no_pol', $noPol)->orderBy('created_at', 'desc')->first();
        return $search;
    }
}
