<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyTenant;
use App\Models\Concerns\HasDiffrntHashId;
use Database\Factories\VehicleInsuranceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class VehicleInsurance extends Model
{
    /** @use HasFactory<VehicleInsuranceFactory> */
    use BelongsToCompanyTenant, HasDiffrntHashId, HasFactory, SoftDeletes;

    protected $table = 'vehicle_insurances';

    protected static function booted(): void
    {
        // company_id always tracks the parent vehicle (denormalized for tenant scope).
        static::saving(function (self $insurance): void {
            $insurance->company_id = $insurance->vehicle?->company_id
                ?? Vehicle::withoutGlobalScopes()->whereKey($insurance->vehicle_id)->value('company_id');
        });

        static::created(fn (self $insurance) => VehicleInsuranceSnapshot::recordFor($insurance));
        static::updated(fn (self $insurance) => VehicleInsuranceSnapshot::recordFor($insurance));
    }

    // Tenant company_id is derived from the vehicle, not assigned from the user.
    public function getTenantAssignmentColumn(): ?string
    {
        return null;
    }

    protected $fillable = [
        'code',
        'vehicle_id',
        'company_id',
        'insurer_name',
        'policy_number',
        'insurance_type',
        'start_date',
        'end_date',
        'coverage_value',
        'premium',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'coverage_value' => 'decimal:2',
            'premium' => 'decimal:2',
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $insurance = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('vehicle_insurances', 'code')->ignore($insurance)->withoutTrashed()],
            'vehicle_id' => ['required', 'integer', Rule::exists(Vehicle::class, 'id'), self::vehicleBelongsToTenantRule()],
            'insurer_name' => ['required', 'string', 'max:255'],
            'policy_number' => ['required', 'string', 'max:255'],
            'insurance_type' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'coverage_value' => ['nullable', 'numeric', 'min:0'],
            'premium' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public static function validationMessages(): array
    {
        return [
            'code.unique' => 'Kode asuransi sudah tersedia.',
            'end_date.after_or_equal' => 'Tanggal berakhir tidak boleh sebelum tanggal mulai.',
        ];
    }

    /**
     * Ensure the selected vehicle belongs to one of the user's tenant companies.
     */
    protected static function vehicleBelongsToTenantRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '' || self::isTenantBypassed()) {
                return;
            }

            $allowedCompanyIds = self::resolveTenantCompanyIds();
            $companyId = Vehicle::withoutGlobalScopes()->whereKey($value)->value('company_id');

            if ($companyId === null || ! in_array((int) $companyId, $allowedCompanyIds, true)) {
                $fail('Kendaraan yang dipilih tidak tersedia untuk perusahaan Anda.');
            }
        };
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(VehicleInsuranceSnapshot::class)->latest();
    }
}
