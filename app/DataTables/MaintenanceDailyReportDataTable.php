<?php

namespace App\DataTables;

use App\Models\MaintenanceDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MaintenanceDailyReportDataTable extends StaticReportDataTable
{
    /**
     * @param  Collection<int, MaintenanceDetail>  $detailMtc
     */
    public function __construct(
        private Collection $detailMtc,
    ) {}

    public function id(): string
    {
        return 'maintenance-daily-report';
    }

    public function tabulatorColumns(): array
    {
        $money = ['symbol' => '', 'thousand' => '.', 'decimal' => ',', 'precision' => 0];

        return [
            ['title' => 'No', 'formatter' => 'rownum', 'hozAlign' => 'center', 'headerSort' => false, 'width' => 52, 'download' => false],
            ['title' => 'TANGGAL', 'field' => 'tanggal'],
            ['title' => 'KODE MT', 'field' => 'code_mt'],
            ['title' => 'JENIS', 'field' => 'maintenance_name', 'bottomCalc' => 'staticLabel', 'bottomCalcParams' => ['label' => 'JUMLAH']],
            ['title' => 'BIAYA', 'columns' => [
                ['title' => 'QTY', 'field' => 'qty', 'hozAlign' => 'right'],
                ['title' => 'HARGA', 'field' => 'price', 'hozAlign' => 'right',
                    'formatter' => 'money', 'formatterParams' => $money],
                ['title' => 'SUBTOTAL', 'field' => 'subtotal', 'hozAlign' => 'right',
                    'formatter' => 'money', 'formatterParams' => $money,
                    'bottomCalc' => 'sum', 'bottomCalcFormatter' => 'money', 'bottomCalcFormatterParams' => $money],
            ]],
        ];
    }

    public function clientData(): array
    {
        return $this->detailMtc
            ->map(function ($row): array {
                $date = $row->date_out ?? $row->date_in;

                return [
                    'tanggal' => $date ? Carbon::parse($date)->format('d-m-Y') : '-',
                    'code_mt' => $row->code_mt ?? '-',
                    'maintenance_name' => $row->maintenance_name,
                    'qty' => (float) ($row->qty ?: 0),
                    'price' => (float) ($row->price ?? 0),
                    'subtotal' => (float) ($row->subtotal ?? 0),
                ];
            })
            ->values()
            ->all();
    }
}
