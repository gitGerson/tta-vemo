<?php

namespace App\Exports;

use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class MaintenanceMonthlyAnalysisExport implements FromCollection, ShouldAutoSize, WithEvents
{
    /**
     * @param  Collection<int, array<string, mixed>>  $rows  Pre-aggregated per-vehicle rows.
     */
    public function __construct(
        protected Collection $rows,
        protected ?Company $company,
        protected Carbon $date,
        protected ?string $unit = null,
    ) {}

    public function collection()
    {
        return $this->rows->map(fn (array $row): array => [
            $row['no_pol'],
            $row['jenis'],
            (int) $row['maintenance_count'],
            (float) $row['total_cost'],
        ]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:D1');
                $sheet->setCellValue('A1', 'Laporan Analisa Bulanan Pemeliharaan');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->setCellValue('A2', 'Perusahaan:');
                $sheet->setCellValue('B2', $this->company?->name ?? 'Semua Perusahaan');

                $sheet->setCellValue('A3', 'Periode:');
                $sheet->setCellValue('B3', $this->date->locale('id')->translatedFormat('F Y'));

                $sheet->setCellValue('A4', 'Unit:');
                $sheet->setCellValue('B4', $this->unit ?: 'Semua Unit');

                $sheet->fromArray([
                    [
                        'No Polisi',
                        'Jenis',
                        'Jumlah Pemeliharaan',
                        'Total Biaya',
                    ],
                ], null, 'A6');
            },
        ];
    }
}
