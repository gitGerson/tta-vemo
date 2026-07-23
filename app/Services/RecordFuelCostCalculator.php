<?php

namespace App\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class RecordFuelCostCalculator
{
    public function calculate(string $unitPrice, string $liters): string
    {
        return BigDecimal::of($unitPrice)
            ->multipliedBy($liters)
            ->toScale(2, RoundingMode::HalfUp)
            ->toString();
    }
}
