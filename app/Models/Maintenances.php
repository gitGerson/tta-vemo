<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenances extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maintenances';
    protected $guarded = [''];

    public function request()
    {
        return $this->belongsTo(User::class, 'get_request');
    }
    public function approved()
    {
        return $this->belongsTo(User::class, 'get_approval');
    }

    static function getMtcByPlat($plat)
    {
        return Maintenances::where('no_pol', $plat)->first();
    }
}
