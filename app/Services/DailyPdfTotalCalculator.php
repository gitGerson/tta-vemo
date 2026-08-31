<?php

namespace App\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class DailyPdfTotalCalculator
{
    /**
     * @param  iterable<int, int|float|string>  $amounts
     * @return array{roundedAmounts: list<string>, roundedTotal: string}
     */
    public function calculate(iterable $amounts): array
    {
        $roundedAmounts = [];
        $roundedTotal = BigDecimal::zero();

        foreach ($amounts as $amount) {
            $roundedAmount = BigDecimal::of($amount)
                ->dividedBy(100, 0, RoundingMode::Ceiling)
                ->multipliedBy(100);

            $roundedAmounts[] = $roundedAmount->toString();
            $roundedTotal = $roundedTotal->plus($roundedAmount);
        }

        return [
            'roundedAmounts' => $roundedAmounts,
            'roundedTotal' => $roundedTotal->toString(),
        ];
    }
}
