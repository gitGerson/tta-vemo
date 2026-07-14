<?php

namespace App\Services;

use Vinkla\Hashids\HashidsManager;

class DiffrntHashId
{
    protected array $encodeCache = [];

    protected array $decodeCache = [];

    public function __construct(protected HashidsManager $hashids)
    {
    }

    public function encode(int|string $id, ?string $namespace = null): string
    {
        if (! is_int($id) && ! (is_string($id) && ctype_digit($id))) {
            return '';
        }

        $cacheKey = $this->makeEncodeCacheKey($id, $namespace);

        if (array_key_exists($cacheKey, $this->encodeCache)) {
            return $this->encodeCache[$cacheKey];
        }

        return $this->encodeCache[$cacheKey] = $this->hashids->encode((int) $id);
    }

    public function decode(?string $hashid): ?int
    {
        if (! is_string($hashid) || trim($hashid) === '') {
            return null;
        }

        $hashid = trim($hashid);

        if (ctype_digit($hashid)) {
            return null;
        }

        if (array_key_exists($hashid, $this->decodeCache)) {
            return $this->decodeCache[$hashid];
        }

        $decoded = $this->hashids->decode($hashid);
        $id = $decoded[0] ?? null;

        return $this->decodeCache[$hashid] = is_int($id) ? $id : null;
    }

    protected function makeEncodeCacheKey(int|string $id, ?string $namespace = null): string
    {
        return $namespace === null ? (string) $id : "{$namespace}:{$id}";
    }
}
