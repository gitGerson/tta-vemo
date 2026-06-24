<?php

namespace App\Models;

use App\Models\Concerns\HasDiffrntHashId;
use App\Support\S3Url;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasDiffrntHashId, HasFactory;

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

    public function imageUrl(): ?string
    {
        return S3Url::temporary($this->image);
    }
}
