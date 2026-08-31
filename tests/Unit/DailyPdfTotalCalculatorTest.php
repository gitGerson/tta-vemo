<?php

namespace Tests\Unit;

use App\Services\DailyPdfTotalCalculator;
use PHPUnit\Framework\TestCase;

class DailyPdfTotalCalculatorTest extends TestCase
{
    public function test_it_rounds_each_record_up_to_the_next_hundred_before_summing(): void
    {
        $totals = (new DailyPdfTotalCalculator)->calculate(['101', '199', '200']);

        $this->assertSame([
            'roundedAmounts' => ['200', '200', '200'],
            'roundedTotal' => '600',
        ], $totals);
    }

    public function test_it_rounds_decimal_amounts_directly_to_the_next_hundred(): void
    {
        $totals = (new DailyPdfTotalCalculator)->calculate(['100.01']);

        $this->assertSame([
            'roundedAmounts' => ['200'],
            'roundedTotal' => '200',
        ], $totals);
    }

    public function test_it_keeps_exact_hundreds_and_zero_unchanged(): void
    {
        $totals = (new DailyPdfTotalCalculator)->calculate(['0', '6643700']);

        $this->assertSame([
            'roundedAmounts' => ['0', '6643700'],
            'roundedTotal' => '6643700',
        ], $totals);
    }
}
