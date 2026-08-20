<?php

namespace App\DataTables;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class MaintenanceYearReportDataTable extends StaticReportDataTable
{
    private const MONTHS = [
        1 => 'JANUARI', 2 => 'FEBRUARI', 3 => 'MARET', 4 => 'APRIL',
        5 => 'MEI', 6 => 'JUNI', 7 => 'JULI', 8 => 'AGUSTUS',
        9 => 'SEPTEMBER', 10 => 'OKTOBER', 11 => 'NOVEMBER', 12 => 'DESEMBER',
    ];

    /**
     * @param  Collection<int, Maintenance>  $mtc  Rows of {no_pol, month, total_amount}.
     * @param  Collection<int, Vehicle>  $vehicles
     */
    public function __construct(
        private Collection $mtc,
        private Collection $vehicles,
    ) {}

    public function id(): string
    {
        return 'maintenance-year-report';
    }

    public function tabulatorColumns(): array
    {
        $money = ['symbol' => '', 'thousand' => '.', 'decimal' => ',', 'precision' => 0];

        $columns = [
            ['title' => 'No', 'formatter' => 'rownum', 'hozAlign' => 'center', 'headerSort' => false, 'width' => 52, 'download' => false],
            ['title' => 'KENDARAAN', 'field' => 'kendaraan', 'bottomCalc' => 'staticLabel', 'bottomCalcParams' => ['label' => 'TOTAL']],
            ['title' => 'TAHUN MASUK', 'field' => 'tahun_masuk', 'hozAlign' => 'center'],
        ];

        foreach (self::MONTHS as $index => $label) {
            $columns[] = [
                'title' => $label,
                'field' => 'm'.$index,
                'hozAlign' => 'right',
                'formatter' => 'money',
                'formatterParams' => $money,
                'bottomCalc' => 'sum',
                'bottomCalcFormatter' => 'money',
                'bottomCalcFormatterParams' => $money,
            ];
        }

        $columns[] = [
            'title' => 'TOTAL',
            'field' => 'total',
            'hozAlign' => 'right',
            'formatter' => 'money',
            'formatterParams' => $money,
            'bottomCalc' => 'sum',
            'bottomCalcFormatter' => 'money',
            'bottomCalcFormatterParams' => $money,
        ];

        return $columns;
    }

    public function clientData(): array
    {
        return $this->mtc
            ->groupBy('no_pol')
            ->map(function (Collection $rows, string $noPol): array {
                $vehicle = $this->vehicles->firstWhere('plat_new', $noPol);

                $record = [
                    'kendaraan' => $vehicle ? $vehicle->plat_new.' / '.$vehicle->type : 'Unknown Vehicle',
                    'tahun_masuk' => $vehicle?->entry_year ?? '-',
                ];

                $total = 0.0;
                for ($month = 1; $month <= 12; $month++) {
                    $record['m'.$month] = 0.0;
                }

                foreach ($rows as $row) {
                    $amount = (float) $row->total_amount;
                    $record['m'.$row->month] += $amount;
                    $total += $amount;
                }

                $record['total'] = $total;

                return $record;
            })
            ->values()
            ->all();
    }
}
