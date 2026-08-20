<?php

namespace App\Models;

use App\Models\Concerns\HasDiffrntHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationReadReceipt extends Model
{
    use HasDiffrntHashId;

    protected $fillable = [
        'user_id',
        'notification_key',
        'notification_type',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
