<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory;

    protected $table = 'note';

    protected $fillable = [
        'maintenances_id',
        'image',
        'url',
    ];

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class, 'maintenances_id');
    }
}
