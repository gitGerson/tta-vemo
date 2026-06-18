<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use App\Models\Concerns\BelongsToCompanyTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Maintenance extends Model
{
    use Auditable, BelongsToCompanyTenant, HasFactory, SoftDeletes;

    protected $table = 'maintenances';

    public const STATUS_DRAFT = 'DRAFT';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_CANCELLED = 'CANCELLED';

    protected static function booted(): void
    {
        // company_id always tracks the vehicle that owns the plate (denormalized
        // for tenant scope). Maintenances link to vehicles by plate, not an FK.
        static::saving(function (self $maintenance): void {
            $maintenance->company_id = Vehicle::withoutGlobalScopes()
                ->where('plat_new', $maintenance->no_pol)
                ->value('company_id');
        });
    }

    // Tenant company_id is derived from the vehicle, not assigned from the user.
    public function getTenantAssignmentColumn(): ?string
    {
        return null;
    }

    protected $fillable = [
        'code_mt',
        'no_pol',
        'company_id',
        'total_amount',
        'date_in',
        'date_check',
        'date_out',
        'mileage',
        'description',
        'isServiceRoutine',
        'status',
        'viewed_at',
        'viewed_by',
        'is_confirmed',
        'confirmed_at',
        'confirmed_by',
        'confirmation_notes',
        'requested_by_name',
    ];

    protected function casts(): array
    {
        return [
            'date_in' => 'date',
            'date_check' => 'date',
            'date_out' => 'date',
            'isServiceRoutine' => 'boolean',
            'is_confirmed' => 'boolean',
            'viewed_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(MaintenanceDetail::class, 'maintenance_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'maintenances_id');
    }

    public function viewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viewed_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->details()->sum('subtotal');
        $this->save();
    }

    public function isReadOnly(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED], true);
    }

    public static function generateCode(): string
    {
        return 'MT-'.now()->timestamp;
    }
}
