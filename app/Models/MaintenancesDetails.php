<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenancesDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'maintenances_details';

    protected $guarded = [''];

    public function maintenance()
    {
        return $this->belongsTo(Maintenances::class, 'maintenance_id');
    }

    /**
     * Calculate and set subtotal based on price and qty
     */
    public function calculateSubtotal()
    {
        $this->subtotal = $this->price * ($this->qty ?? 1);
    }
}
