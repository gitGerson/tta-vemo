<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationService extends Model
{
    use HasFactory;
    protected $table = 'notification_service';
    protected $guarded = [''];
}
