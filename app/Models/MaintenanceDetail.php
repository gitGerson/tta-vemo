<?php

namespace App\Models;

use App\Models\Concerns\HasDiffrntHashId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceDetail extends Model
{
    use HasDiffrntHashId, HasFactory, SoftDeletes;

    protected $table = 'maintenances_details';

    protected $fillable = [
        'maintenance_id',
        'maintenance_name',
        'price',
        'qty',
        'subtotal',
        'material',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_id');
    }
}
