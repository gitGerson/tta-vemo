<?php

namespace App\Models;

use App\Models\Concerns\HasDiffrntHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailNotificationLog extends Model
{
    use HasDiffrntHashId;

    protected $fillable = [
        'user_id',
        'notification_key',
        'notification_type',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
