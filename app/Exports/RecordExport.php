<?php

namespace App\Exports;

use App\Models\Company;
use App\Models\Record;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeSheet;

class RecordExport implements FromCollection, ShouldAutoSize, WithEvents
{
    public function __construct(
        protected string $startDate,
        protected string $endDate,
        protected ?int $companyId = null,
    ) {}

    public function collection()
    {
        return Record::with('company')
            ->when($this->companyId, fn ($query) => $query->where('company_id', $this->companyId))
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->get()
            ->map(fn (Record $item): array => [
                $item->date?->format('Y-m-d'),
                $item->driver_1,
                $item->driver_2,
                $item->no_pol,
                $item->destination,
                $item->start_km,
                $item->finish_km,
                $item->totals,
                $item->bbm,
                $item->fuel_unit_price,
                $item->idr,
                $item->scale,
                $item->company->name ?? '-',
            ]);
    }

    protected function getCompanyName(): string
    {
        if (! $this->companyId) {
            return 'Semua Perusahaan';
        }

        return Company::find($this->companyId)?->name ?? 'Tidak ditemukan';
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:M1');
                $sheet->setCellValue('A1', 'Laporan Perjalanan Kendaraan Operasional');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->setCellValue('A2', 'Tanggal Mulai:');
                $sheet->setCellValue('B2', Carbon::parse($this->startDate)->format('d-m-Y'));

                $sheet->setCellValue('A3', 'Tanggal Selesai:');
                $sheet->setCellValue('B3', Carbon::parse($this->endDate)->format('d-m-Y'));

                $sheet->setCellValue('A4', 'Perusahaan:');
                $sheet->setCellValue('B4', $this->getCompanyName());

                $sheet->fromArray([
                    [
                        'Tanggal',
                        'Driver 1',
                        'Driver 2',
                        'No Polisi',
                        'Tujuan',
                        'Start KM',
                        'Finish KM',
                        'Total KM',
                        'Jumlah BBM (Liter)',
                        'Harga per Liter',
                        'Total Harga',
                        'Skala (KM/L)',
                        'Perusahaan',
                    ],
                ], null, 'A6');
            },
        ];
    }
}
