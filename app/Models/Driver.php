<?php

namespace App\Models;

use App\Rules\DifferentPhoneNumbers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class Driver extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'drivers';

    protected $fillable = [
        'image',
        'nik',
        'name',
        'address',
        'no_identity',
        'residence',
        'date_birth',
        'no_phone_1',
        'no_phone_2',
        'entry_date',
        'out_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_birth' => 'date',
            'entry_date' => 'date',
            'out_date' => 'date',
            'deleted_at' => 'datetime',
        ];
    }

    public static function validationRules(?self $driver = null, ?string $firstPhoneNumber = null): array
    {
        $ignoreId = $driver?->getKey();

        return [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'string', Rule::unique('drivers', 'nik')->ignore($ignoreId)],
            'address' => ['required', 'string', 'max:255'],
            'no_identity' => ['required', 'digits:16', Rule::unique('drivers', 'no_identity')->ignore($ignoreId)],
            'residence' => ['required', 'string', 'max:255'],
            'date_birth' => ['required', 'date'],
            'no_phone_1' => ['required', 'string', 'min:5', 'max:15', Rule::unique('drivers', 'no_phone_1')->ignore($ignoreId)],
            'no_phone_2' => [
                'nullable',
                'string',
                'min:5',
                'max:15',
                Rule::unique('drivers', 'no_phone_2')->ignore($ignoreId),
                new DifferentPhoneNumbers($firstPhoneNumber),
            ],
            'entry_date' => ['required', 'date'],
            'out_date' => ['nullable', 'date', 'after_or_equal:entry_date'],
            'status' => ['required', Rule::in(['ACTIVE', 'PASSIVE'])],
            'company_ids' => ['nullable', 'array'],
            'company_ids.*' => ['integer', Rule::exists(Company::class, 'id')],
        ];
    }

    public static function validationMessages(?self $driver = null): array
    {
        return [
            'no_phone_1.unique' => 'No HP sudah tersedia.',
            'no_phone_2.unique' => 'No HP wali sudah tersedia.',
            'no_identity.digits' => 'No Identitas harus 16 digit.',
        ];
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return Storage::disk('s3')->url($this->image);
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_driver')->withTimestamps();
    }
}
