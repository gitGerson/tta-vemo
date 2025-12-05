<?php

namespace App\Exports;

use App\Models\Record;
use App\Models\Company;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\BeforeSheet;

class RecordExport implements FromCollection, WithEvents, ShouldAutoSize
{
    protected $startDate, $endDate, $companyId;

    public function __construct($startDate, $endDate, $companyId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->companyId = $companyId;
    }

    public function collection()
    {
        return Record::with('company')
            ->when($this->companyId, function ($query) {
                $query->where('company_id', $this->companyId);
            })
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($item) {
                return [
                    $item->date,
                    $item->driver_1,
                    $item->driver_2,
                    $item->no_pol,
                    $item->destination,
                    $item->start_km,
                    $item->finish_km,
                    $item->totals,
                    $item->bbm,
                    $item->idr,
                    $item->scale,
                    $item->company->name ?? '-',
                ];
            });
    }

    protected function getCompanyName() {
        $company = Company::find($this->companyId);
        if (!$this->companyId) {
            return 'Semua Perusahaan';
        }

        $company = \App\Models\Company::find($this->companyId);
        return $company ? $company->name : 'Tidak ditemukan';
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Title
                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'Laporan Perjalanan Kendaraan Operasional');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // Info tanggal & perusahaan
                $sheet->setCellValue('A2', 'Tanggal Mulai:');
                $sheet->setCellValue('B2', Carbon::parse($this->startDate)->format('d-m-Y'));

                $sheet->setCellValue('A3', 'Tanggal Selesai:');
                $sheet->setCellValue('B3', Carbon::parse($this->endDate)->format('d-m-Y'));

                $sheet->setCellValue('A4', 'Perusahaan:');
                $sheet->setCellValue('B4', $this->getCompanyName());

                // Baris kosong di bawah perusahaan (baris 5 dibiarkan kosong)

                // Header kolom mulai baris 6
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
                        'BBM',
                        'IDR',
                        'Skala (KM/L)',
                        'Perusahaan',
                    ]
                ], null, 'A6');
            },
        ];
    }
}
