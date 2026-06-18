<?php

namespace App\Http\Requests;

use App\Services\FilePondUploadService;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class FormDemoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $filePondUploads = app(FilePondUploadService::class);

        return [
            'title' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:draft,review,published'],
            'role_template' => ['nullable', 'string', 'in:admin_template,user_manager_template,viewer_template'],
            'featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'publish_date' => ['nullable', 'date'],
            'event_date' => ['nullable', 'string', 'max:255'],
            'booking_window' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string', 'max:10000'],
            'attachment_native' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:2048'],
            'attachment_filepond' => [
                'nullable',
                function (string $attribute, mixed $value, Closure $fail) use ($filePondUploads): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    if ($value instanceof UploadedFile) {
                        validator(
                            ['upload' => $value],
                            ['upload' => ['file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:2048']]
                        )->validate();

                        return;
                    }

                    ($filePondUploads->tempPathValidationRule())($attribute, $value, $fail);
                },
            ],
            'attachment_filepond_existing' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Title wajib diisi.',
            'quantity.required' => 'Quantity wajib diisi.',
            'status.required' => 'Status wajib dipilih.',
            'attachment_native.max' => 'File native maksimal 2MB.',
        ];
    }
}
