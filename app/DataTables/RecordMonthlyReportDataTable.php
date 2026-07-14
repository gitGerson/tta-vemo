<?php

namespace App\DataTables;

use App\Models\Record;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecordMonthlyReportDataTable extends StaticReportDataTable
{
    /**
     * @param  Collection<int, Record>  $records
     * @param  Collection<int, Vehicle>  $vehicles
     */
    public function __construct(
        private Collection $records,
        private Collection $vehicles,
    ) {}

    public function id(): string
    {
        return 'record-monthly-report';
    }

    public function tabulatorColumns(): array
    {
        $money = ['symbol' => '', 'thousand' => '.', 'decimal' => ',', 'precision' => 0];

        return [
            ['title' => 'No', 'formatter' => 'rownum', 'hozAlign' => 'center', 'headerSort' => false, 'width' => 52, 'download' => false],
            ['title' => 'TANGGAL', 'field' => 'tanggal'],
            ['title' => 'JENIS', 'field' => 'jenis'],
            ['title' => 'NOPOL', 'field' => 'no_pol'],
            ['title' => 'TUJUAN', 'field' => 'tujuan', 'bottomCalc' => 'staticLabel', 'bottomCalcParams' => ['label' => 'JUMLAH']],
            ['title' => 'KILOMETER', 'columns' => [
                ['title' => 'AWAL', 'field' => 'start_km', 'hozAlign' => 'right'],
                ['title' => 'AKHIR', 'field' => 'finish_km', 'hozAlign' => 'right'],
                ['title' => 'TOTAL', 'field' => 'totals', 'hozAlign' => 'right', 'bottomCalc' => 'sum'],
            ]],
            ['title' => 'LITER', 'field' => 'bbm', 'hozAlign' => 'right', 'bottomCalc' => 'sum'],
            ['title' => 'RUPIAH', 'field' => 'idr', 'hozAlign' => 'right',
                'formatter' => 'money', 'formatterParams' => $money,
                'bottomCalc' => 'sum', 'bottomCalcFormatter' => 'money', 'bottomCalcFormatterParams' => $money],
            ['title' => 'SUPIR', 'field' => 'supir'],
            ['title' => 'SKALA', 'field' => 'scale', 'hozAlign' => 'center'],
        ];
    }

    public function clientData(): array
    {
        $vehiclesByPlate = $this->vehicles
            ->filter(fn ($vehicle) => filled($vehicle->plat_new))
            ->keyBy('plat_new');

        return $this->records
            ->map(function ($record) use ($vehiclesByPlate): array {
                $vehicle = $vehiclesByPlate->get($record->no_pol);

                return [
                    'tanggal' => Carbon::parse($record->date)->format('d-m-Y'),
                    'jenis' => $vehicle->type ?? '-',
                    'no_pol' => $record->no_pol,
                    'tujuan' => $record->destination,
                    'start_km' => (float) ($record->start_km ?? 0),
                    'finish_km' => (float) ($record->finish_km ?? 0),
                    'totals' => (float) ($record->totals ?? 0),
                    'bbm' => (float) ($record->bbm ?? 0),
                    'idr' => (float) ($record->idr ?? 0),
                    'supir' => strtoupper($record->driver_1 ?: 'NONE').' - '.strtoupper($record->driver_2 ?: 'NONE'),
                    'scale' => $record->scale ?? '-',
                ];
            })
            ->values()
            ->all();
    }
}
