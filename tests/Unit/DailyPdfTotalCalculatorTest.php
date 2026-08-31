<?php

namespace Tests\Unit;

use App\Services\DailyPdfTotalCalculator;
use PHPUnit\Framework\TestCase;

class DailyPdfTotalCalculatorTest extends TestCase
{
    public function test_it_rounds_the_displayed_subtotal_up_to_the_next_hundred(): void
    {
        $totals = (new DailyPdfTotalCalculator)->calculate(['6643627']);

        $this->assertSame([
            'subtotal' => '6643627',
            'roundingAdjustment' => '73',
            'roundedTotal' => '6643700',
        ], $totals);
    }

    public function test_it_sums_the_same_whole_rupiah_values_displayed_for_each_record(): void
    {
        $totals = (new DailyPdfTotalCalculator)->calculate(['100.49', '100.51']);

        $this->assertSame([
            'subtotal' => '201',
            'roundingAdjustment' => '99',
            'roundedTotal' => '300',
        ], $totals);
    }

    public function test_it_does_not_adjust_an_existing_hundred(): void
    {
        $totals = (new DailyPdfTotalCalculator)->calculate(['6643700']);

        $this->assertSame([
            'subtotal' => '6643700',
            'roundingAdjustment' => '0',
            'roundedTotal' => '6643700',
        ], $totals);
    }
}
