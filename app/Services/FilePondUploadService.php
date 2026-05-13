<?php

namespace App\Services;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FilePondUploadService
{
    public function __construct(
        protected string $tempDisk = 'local',
        protected string $tempPrefix = 'tmp/filepond/'
    ) {}

    public function isTempPath(mixed $value): bool
    {
        return is_string($value) && str_starts_with($value, $this->tempPrefix);
    }

    public function tempPathValidationRule(array $allowedMimeTypes = [], ?int $maxKilobytes = null): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($allowedMimeTypes, $maxKilobytes): void {
            if (! $this->isTempPath($value)) {
                $fail('Format upload tidak valid.');

                return;
            }

            if (! Storage::disk($this->tempDisk)->exists($value)) {
                $fail('File upload sementara tidak ditemukan. Silakan upload ulang.');

                return;
            }

            if ($maxKilobytes !== null && Storage::disk($this->tempDisk)->size($value) > ($maxKilobytes * 1024)) {
                $fail('Ukuran file terlalu besar.');

                return;
            }

            if ($allowedMimeTypes !== []) {
                $fullPath = Storage::disk($this->tempDisk)->path($value);
                $mimeType = mime_content_type($fullPath) ?: 'application/octet-stream';

                if (! in_array($mimeType, $allowedMimeTypes, true)) {
                    $fail('Format file tidak valid.');
                }
            }
        };
    }

    public function imageUploadValidationRule(
        int $maxKilobytes = 2048,
        array $mimeExtensions = ['jpeg', 'png', 'jpg', 'gif', 'webp'],
        array $existingReferences = []
    ): Closure {
        $allowedMimeTypes = [
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
        ];

        $allowedMimeTypes = array_values(array_unique(array_filter(array_map(
            fn (string $extension): ?string => $allowedMimeTypes[strtolower($extension)] ?? null,
            $mimeExtensions
        ))));

        $existingReferences = array_values(array_filter($existingReferences));

        return function (string $attribute, mixed $value, Closure $fail) use ($allowedMimeTypes, $existingReferences, $maxKilobytes, $mimeExtensions): void {
            if ($value === null || $value === '') {
                return;
            }

            if ($value instanceof UploadedFile) {
                $validator = validator(
                    ['upload' => $value],
                    ['upload' => ['image', 'mimes:'.implode(',', $mimeExtensions), 'max:'.$maxKilobytes]]
                );

                if ($validator->fails()) {
                    $fail($validator->errors()->first('upload'));
                }

                return;
            }

            if (is_string($value) && in_array($value, $existingReferences, true)) {
                return;
            }

            $this->tempPathValidationRule($allowedMimeTypes, $maxKilobytes)($attribute, $value, $fail);
        };
    }

    public function storeTemporaryUpload(UploadedFile $uploadedFile): string
    {
        $tempPath = $this->tempPrefix.Str::uuid()->toString().'.'.$uploadedFile->getClientOriginalExtension();

        Storage::disk($this->tempDisk)->put($tempPath, file_get_contents($uploadedFile->getRealPath()));

        return $tempPath;
    }

    public function deleteTemporaryUpload(string $tempPath): void
    {
        if (! $this->isTempPath($tempPath)) {
            return;
        }

        if (Storage::disk($this->tempDisk)->exists($tempPath)) {
            Storage::disk($this->tempDisk)->delete($tempPath);
        }
    }

    public function getTemporaryUpload(string $tempPath): ?array
    {
        if (! $this->isTempPath($tempPath) || ! Storage::disk($this->tempDisk)->exists($tempPath)) {
            return null;
        }

        $fullPath = Storage::disk($this->tempDisk)->path($tempPath);

        return [
            'contents' => Storage::disk($this->tempDisk)->get($tempPath),
            'mime_type' => mime_content_type($fullPath) ?: 'application/octet-stream',
            'filename' => basename($tempPath),
        ];
    }

    public function storeFromRequestField(
        Request $request,
        string $field,
        string $directory,
        string $targetDisk = 'local'
    ): ?string {
        if ($request->hasFile($field)) {
            $file = $request->file($field);

            if (! $file instanceof UploadedFile) {
                return null;
            }

            return $this->storeUploadedFile($file, $directory, $targetDisk);
        }

        $tempPath = $request->input($field);

        if (! $this->isTempPath($tempPath) || ! Storage::disk($this->tempDisk)->exists($tempPath)) {
            return null;
        }

        return $this->storeTempFile($tempPath, $directory, $targetDisk);
    }

    /**
     * @return array<int, string>
     */
    public function storeManyFromRequestField(
        Request $request,
        string $field,
        string $directory,
        string $targetDisk = 'local'
    ): array {
        $paths = [];

        if ($request->hasFile($field)) {
            $files = $request->file($field);

            if ($files instanceof UploadedFile) {
                $files = [$files];
            }

            if (is_array($files)) {
                foreach ($files as $file) {
                    if (! $file instanceof UploadedFile) {
                        continue;
                    }

                    $paths[] = $this->storeUploadedFile($file, $directory, $targetDisk);
                }
            }

            return array_values(array_filter($paths));
        }

        $tempPaths = $request->input($field, []);

        if (is_string($tempPaths)) {
            $tempPaths = [$tempPaths];
        }

        if (! is_array($tempPaths)) {
            return [];
        }

        foreach ($tempPaths as $tempPath) {
            if (! $this->isTempPath($tempPath) || ! Storage::disk($this->tempDisk)->exists($tempPath)) {
                continue;
            }

            $paths[] = $this->storeTempFile($tempPath, $directory, $targetDisk);
        }

        return array_values(array_filter($paths));
    }

    protected function storeUploadedFile(UploadedFile $file, string $directory, string $targetDisk): string
    {
        $fileName = time().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();
        $filePath = trim($directory, '/').'/'.$fileName;

        Storage::disk($targetDisk)->put($filePath, file_get_contents($file));

        return $filePath;
    }

    protected function storeTempFile(string $tempPath, string $directory, string $targetDisk): string
    {
        $extension = pathinfo($tempPath, PATHINFO_EXTENSION) ?: 'bin';
        $filePath = trim($directory, '/').'/'.time().'_'.Str::random(16).'.'.$extension;

        Storage::disk($targetDisk)->put($filePath, Storage::disk($this->tempDisk)->get($tempPath));
        Storage::disk($this->tempDisk)->delete($tempPath);

        return $filePath;
    }
}
