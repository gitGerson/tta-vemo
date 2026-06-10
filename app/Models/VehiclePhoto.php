<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyTenant;
use Closure;
use Database\Factories\VehiclePhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehiclePhoto extends Model
{
    /** @use HasFactory<VehiclePhotoFactory> */
    use BelongsToCompanyTenant, HasFactory, SoftDeletes;

    protected $table = 'vehicle_photos';

    protected static function booted(): void
    {
        // company_id always tracks the parent vehicle (denormalized for tenant scope).
        static::saving(function (self $photo): void {
            $photo->company_id = $photo->vehicle?->company_id
                ?? Vehicle::withoutGlobalScopes()->whereKey($photo->vehicle_id)->value('company_id');
        });
    }

    // Tenant company_id is derived from the vehicle, not assigned from the user.
    public function getTenantAssignmentColumn(): ?string
    {
        return null;
    }

    protected $fillable = [
        'vehicle_id',
        'company_id',
        'period',
        'photos',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'photos' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $photo = null): array
    {
        return [
            'vehicle_id' => ['required', 'integer', Rule::exists(Vehicle::class, 'id'), self::vehicleBelongsToTenantRule()],
            'period' => [
                'required',
                'date',
                self::uniqueMonthPerVehicleRule($photo),
            ],
            'photos' => ['nullable', 'array'],
            'notes' => ['required', 'string'],
        ];
    }

    public static function validationMessages(): array
    {
        return [];
    }

    /**
     * One photo record per vehicle per month. Compared with whereDate because the
     * `date` cast persists `period` as a full datetime (YYYY-MM-DD 00:00:00).
     */
    protected static function uniqueMonthPerVehicleRule(?self $photo): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($photo): void {
            if (blank($value) || blank(request()->input('vehicle_id'))) {
                return;
            }

            $exists = self::withoutGlobalScopes()
                ->where('vehicle_id', request()->input('vehicle_id'))
                ->whereDate('period', $value)
                ->when($photo, fn ($query) => $query->whereKeyNot($photo->getKey()))
                ->exists();

            if ($exists) {
                $fail('Foto untuk kendaraan ini pada bulan tersebut sudah ada.');
            }
        };
    }

    /**
     * Ensure the selected vehicle belongs to one of the user's tenant companies.
     */
    protected static function vehicleBelongsToTenantRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
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

    /**
     * Resolve the stored photo paths to absolute URLs (S3), passing through full URLs.
     *
     * @return array<int, string>
     */
    public function photoUrls(): array
    {
        return collect($this->photos ?? [])
            ->filter()
            ->map(fn (string $path): string => str_starts_with($path, 'http')
                ? $path
                : Storage::disk('s3')->url($path))
            ->values()
            ->all();
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
