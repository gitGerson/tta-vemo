<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('operational.maintenances.create') ?? false;
    }

    public function isDraft(): bool
    {
        return $this->boolean('save_as_draft');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->isDraft()) {
            return [
                'no_pol' => ['nullable', 'string'],
                'date_in' => ['nullable', 'date'],
                'date_out' => ['nullable', 'date'],
                'mileage' => ['nullable', 'string'],
                'desc' => ['nullable', 'string'],
                'isServiceRoutine' => ['nullable'],
                'fields' => ['nullable', 'array'],
                'fields.*' => ['nullable', 'string'],
                'items' => ['nullable', 'array'],
            ];
        }

        return [
            'no_pol' => ['required', 'string'],
            'date_in' => ['required', 'date'],
            'date_out' => ['nullable', 'date'],
            'mileage' => ['required', 'string'],
            'desc' => ['nullable', 'string'],
            'isServiceRoutine' => ['required'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'no_pol.required' => 'Kendaraan wajib dipilih.',
            'date_in.required' => 'Tanggal masuk wajib diisi.',
            'mileage.required' => 'Kilometer wajib diisi.',
            'fields.required' => 'Bukti/nota perbaikan wajib diupload.',
            'fields.min' => 'Minimal 1 bukti harus diupload.',
        ];
    }
}
