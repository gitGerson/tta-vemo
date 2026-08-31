<?php

namespace App\Services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

class DailyPdfTotalCalculator
{
    /**
     * @param  iterable<int, int|float|string>  $amounts
     * @return array{subtotal: string, roundingAdjustment: string, roundedTotal: string}
     */
    public function calculate(iterable $amounts): array
    {
        $subtotal = BigDecimal::zero();

        foreach ($amounts as $amount) {
            $subtotal = $subtotal->plus(
                BigDecimal::of($amount)->toScale(0, RoundingMode::HalfUp),
            );
        }

        $roundedTotal = $subtotal
            ->dividedBy(100, 0, RoundingMode::Ceiling)
            ->multipliedBy(100);

        return [
            'subtotal' => $subtotal->toString(),
            'roundingAdjustment' => $roundedTotal->minus($subtotal)->toString(),
            'roundedTotal' => $roundedTotal->toString(),
        ];
    }
}
