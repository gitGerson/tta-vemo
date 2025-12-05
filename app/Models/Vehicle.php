<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicles';
    protected $guarded = [''];

    static function getByNoPol($plat)
    {
        return static::where('plat_new', $plat)->orWhere('plat_old', $plat)->first();
    }
}
