<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompanyTenant;
use App\Models\Concerns\HasComments;
use App\Models\Concerns\HasDiffrntHashId;
use App\Support\S3Url;
use Closure;
use Database\Factories\VehiclePhotoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class VehiclePhoto extends Model
{
    /** @use HasFactory<VehiclePhotoFactory> */
    use BelongsToCompanyTenant, HasComments, HasDiffrntHashId, HasFactory, SoftDeletes;

    /**
     * The four documented sides of the vehicle (key => label). A month is complete only
     * when every side has an image.
     */
    public const SIDES = [
        'front' => 'Depan',
        'rear' => 'Belakang',
        'left' => 'Samping Kiri',
        'right' => 'Samping Kanan',
    ];

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
            'notes' => ['nullable', 'string'],
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
     * The stored side => path map, limited to known sides with a non-empty path.
     *
     * @return array<string, string>
     */
    public function sidePhotos(): array
    {
        return array_filter(array_intersect_key($this->photos ?? [], self::SIDES));
    }

    /**
     * Resolve a single side's stored path to an absolute S3 URL (passing through full URLs).
     */
    public function sideUrl(string $side): ?string
    {
        $path = $this->photos[$side] ?? null;

        if (! is_string($path) || $path === '') {
            return null;
        }

        return S3Url::temporary($path);
    }

    /**
     * Side keys that have an image for this month.
     *
     * @return array<int, string>
     */
    public function completedSides(): array
    {
        return array_keys($this->sidePhotos());
    }

    public function completedCount(): int
    {
        return count($this->sidePhotos());
    }

    public function isComplete(): bool
    {
        return $this->completedCount() === count(self::SIDES);
    }

    /**
     * Resolve all present side photos to absolute URLs (S3), passing through full URLs.
     *
     * @return array<int, string>
     */
    public function photoUrls(): array
    {
        return collect($this->sidePhotos())
            ->map(fn (string $path): string => S3Url::temporary($path))
            ->values()
            ->all();
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
