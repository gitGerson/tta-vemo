<?php

use App\Services\DiffrntHashId;

if (! function_exists('diffrntEncode')) {
    function diffrntEncode(int|string $id, ?string $namespace = null): string
    {
        return app(DiffrntHashId::class)->encode($id, $namespace);
    }
}

if (! function_exists('diffrntDecode')) {
    function diffrntDecode(?string $hashid): ?int
    {
        return app(DiffrntHashId::class)->decode($hashid);
    }
}
