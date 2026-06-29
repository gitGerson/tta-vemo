<?php

namespace App\DataTables;

use Illuminate\Support\Collection;

class RecordMonthlyAnalysisDataTable extends StaticReportDataTable
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows  Pre-aggregated per-vehicle rows.
     */
    public function __construct(
        private Collection $rows,
    ) {}

    public function id(): string
    {
        return 'record-monthly-analysis';
    }

    public function tabulatorColumns(): array
    {
        $money = ['symbol' => '', 'thousand' => '.', 'decimal' => ',', 'precision' => 0];
        $ratio = ['symbol' => '', 'thousand' => '.', 'decimal' => ',', 'precision' => 2];

        return [
            ['title' => 'No', 'formatter' => 'rownum', 'hozAlign' => 'center', 'headerSort' => false, 'width' => 52, 'download' => false],
            ['title' => 'NOPOL', 'field' => 'no_pol', 'bottomCalc' => 'staticLabel', 'bottomCalcParams' => ['label' => 'JUMLAH']],
            ['title' => 'JENIS', 'field' => 'jenis'],
            ['title' => 'JUMLAH TRIP', 'field' => 'trip', 'hozAlign' => 'right', 'bottomCalc' => 'sum'],
            ['title' => 'TOTAL KM', 'field' => 'total_km', 'hozAlign' => 'right', 'bottomCalc' => 'sum'],
            ['title' => 'TOTAL LITER', 'field' => 'total_liter', 'hozAlign' => 'right', 'bottomCalc' => 'sum'],
            ['title' => 'TOTAL RUPIAH', 'field' => 'total_idr', 'hozAlign' => 'right',
                'formatter' => 'money', 'formatterParams' => $money,
                'bottomCalc' => 'sum', 'bottomCalcFormatter' => 'money', 'bottomCalcFormatterParams' => $money],
            ['title' => 'KONSUMSI (KM/L)', 'field' => 'km_per_liter', 'hozAlign' => 'right',
                'formatter' => 'money', 'formatterParams' => $ratio],
        ];
    }

    public function clientData(): array
    {
        return $this->rows
            ->map(fn (array $row): array => [
                'no_pol' => $row['no_pol'],
                'jenis' => $row['jenis'],
                'trip' => (int) $row['trip'],
                'total_km' => (float) $row['total_km'],
                'total_liter' => (float) $row['total_liter'],
                'total_idr' => (float) $row['total_idr'],
                'km_per_liter' => round((float) $row['km_per_liter'], 2),
            ])
            ->values()
            ->all();
    }
}
