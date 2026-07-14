<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class S3Url
{
    /**
     * Resolve a stored S3 path to a 1-hour pre-signed temporary URL.
     *
     * Values that are already absolute (http...) are passed through unchanged.
     * When the disk cannot create signed URLs (e.g. the local adapter used by
     * Storage::fake in tests) it falls back to the plain ->url().
     */
    public static function temporary(?string $path, int $minutes = 60): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $disk = Storage::disk('s3');

        try {
            return $disk->temporaryUrl($path, now()->addMinutes($minutes));
        } catch (RuntimeException) {
            return $disk->url($path);
        }
    }

    /**
     * Signature-independent identity for a (possibly signed) URL, used to match
     * a re-submitted "kept existing file" against its stored reference. Pre-signed
     * URLs only differ by their query string, so comparing the base URL is stable.
     */
    public static function reference(?string $value): ?string
    {
        return $value === null ? null : Str::before($value, '?');
    }
}
