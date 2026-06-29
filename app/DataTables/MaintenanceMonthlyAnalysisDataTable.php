<?php

namespace App\DataTables;

use Illuminate\Support\Collection;

class MaintenanceMonthlyAnalysisDataTable extends StaticReportDataTable
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows  Pre-aggregated per-vehicle rows.
     */
    public function __construct(
        private Collection $rows,
    ) {}

    public function id(): string
    {
        return 'maintenance-monthly-analysis';
    }

    public function tabulatorColumns(): array
    {
        $money = ['symbol' => '', 'thousand' => '.', 'decimal' => ',', 'precision' => 0];

        return [
            ['title' => 'No', 'formatter' => 'rownum', 'hozAlign' => 'center', 'headerSort' => false, 'width' => 52, 'download' => false],
            ['title' => 'NOPOL', 'field' => 'no_pol', 'bottomCalc' => 'staticLabel', 'bottomCalcParams' => ['label' => 'JUMLAH']],
            ['title' => 'JENIS', 'field' => 'jenis'],
            ['title' => 'JUMLAH PEMELIHARAAN', 'field' => 'maintenance_count', 'hozAlign' => 'right', 'bottomCalc' => 'sum'],
            ['title' => 'TOTAL BIAYA', 'field' => 'total_cost', 'hozAlign' => 'right',
                'formatter' => 'money', 'formatterParams' => $money,
                'bottomCalc' => 'sum', 'bottomCalcFormatter' => 'money', 'bottomCalcFormatterParams' => $money],
        ];
    }

    public function clientData(): array
    {
        return $this->rows
            ->map(fn (array $row): array => [
                'no_pol' => $row['no_pol'],
                'jenis' => $row['jenis'],
                'maintenance_count' => (int) $row['maintenance_count'],
                'total_cost' => (float) $row['total_cost'],
            ])
            ->values()
            ->all();
    }
}
