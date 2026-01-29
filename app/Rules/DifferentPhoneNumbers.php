<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class DifferentPhoneNumbers implements Rule
{
    protected $firstNumber;

    public function __construct(?string $firstNumber = null)
    {
        $this->firstNumber = $firstNumber;
    }

    public function passes($attribute, $value): bool
    {
        if ($this->firstNumber === null) {
            return true;
        }

        return $value !== $this->firstNumber;
    }

    public function message(): string
    {
        return 'No HP 2 harus berbeda dengan No HP 1.';
    }
}
