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

    protected $casts = [
        'is_confirmed' => 'boolean',
        'viewed_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(User::class, 'get_request');
    }

    public function approved()
    {
        return $this->belongsTo(User::class, 'get_approval');
    }

    public function details()
    {
        return $this->hasMany(MaintenancesDetails::class, 'maintenance_id');
    }

    public function viewer()
    {
        return $this->belongsTo(User::class, 'viewed_by');
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function recalculateTotal()
    {
        $this->total_amount = $this->details()->sum('subtotal');
        $this->save();
    }

    public static function getMtcByPlat($plat)
    {
        return Maintenances::where('no_pol', $plat)->first();
    }
}
