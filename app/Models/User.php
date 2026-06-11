<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\HasDiffrntHashId;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use LdapRecord\Laravel\Auth\HasLdapUser;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class User extends Authenticatable implements LdapAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use Auditable, HasDiffrntHashId, HasFactory, Notifiable, SoftDeletes;

    use AuthenticatesWithLdap, HasLdapUser;
    use HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'guid',
        'domain',
        'telegram_chat_id',
        'telegram_link_token',
        'telegram_linked_at',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'telegram_linked_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Destination the Telegram notification channel sends to. Null until the user
     * links their account via the deep-link flow.
     */
    public function routeNotificationForTelegram(): ?string
    {
        return $this->telegram_chat_id;
    }

    public function isTelegramLinked(): bool
    {
        return $this->telegram_chat_id !== null;
    }

    /**
     * Return the existing link token, generating and persisting one if absent. The
     * token is what the bot receives as `/start <token>` to identify this account.
     */
    public function generateTelegramLinkToken(): string
    {
        if ($this->telegram_link_token === null) {
            $this->forceFill(['telegram_link_token' => Str::random(40)])->save();
        }

        return $this->telegram_link_token;
    }

    public function getLdapDomainColumn(): string
    {
        return 'domain';
    }

    public function getLdapGuidColumn(): string
    {
        return 'guid';
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function logs()
    {
        return $this->hasMany(UserLog::class, 'user_id');
    }

    public function targetLogs()
    {
        return $this->hasMany(UserLog::class, 'target_user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot(['assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_user')->withTimestamps();
    }

    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles()->where('roles.name', $role)->exists();
        }

        if (is_object($role)) {
            return $this->roles()->where('roles.id', $role->id)->exists();
        }

        return false;
    }

    public function hasPermission($permission)
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role) {
            $exists = $this->roles()->where('roles.id', $role->id)->exists();

            if (! $exists) {
                $this->roles()->attach($role->id, [
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return $this;
    }

    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role) {
            $this->roles()->detach($role->id);
        }

        return $this;
    }
}
