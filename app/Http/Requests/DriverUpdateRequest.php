<?php

namespace App\Http\Requests;

use App\Models\Driver;
use App\Services\FilePondUploadService;
use Illuminate\Contracts\Validation\ValidationRule;

class DriverUpdateRequest extends DriverStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.drivers.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $driver = $this->driver();
        $rules = Driver::validationRules($driver, $this->input('no_phone_1'));
        $rules['image'] = ['nullable', app(FilePondUploadService::class)->imageUploadValidationRule(
            existingReferences: array_filter([$driver?->imageUrl()]),
        )];

        return $rules;
    }

    public function messages(): array
    {
        return Driver::validationMessages($this->driver());
    }

    protected function driver(): ?Driver
    {
        $driver = $this->route('driver');

        if ($driver instanceof Driver) {
            return $driver;
        }

        return Driver::query()->find($driver);
    }
}
