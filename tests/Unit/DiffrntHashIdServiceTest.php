<?php

namespace Tests\Unit;

use App\Services\DiffrntHashId;
use Tests\TestCase;

class DiffrntHashIdServiceTest extends TestCase
{
    public function test_it_can_encode_and_decode_a_numeric_identifier(): void
    {
        $service = app(DiffrntHashId::class);

        $hashid = $service->encode(15, 'users');

        $this->assertIsString($hashid);
        $this->assertNotSame('15', $hashid);
        $this->assertSame(15, $service->decode($hashid));
    }

    public function test_it_returns_empty_string_for_invalid_encode_input(): void
    {
        $service = app(DiffrntHashId::class);

        $this->assertSame('', $service->encode('abc'));
    }

    public function test_it_returns_null_for_invalid_or_short_hash_input(): void
    {
        $service = app(DiffrntHashId::class);

        $this->assertNull($service->decode(null));
        $this->assertNull($service->decode(''));
        $this->assertNull($service->decode('1'));
    }
}
