<?php

namespace App\Exports;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

/**
 * Report generator export: the monthly BBM report layout (title, company /
 * period / unit meta, grouped KILOMETER header, JUMLAH row) as an .xlsx sheet.
 */
class RecordReportGeneratorExport implements FromCollection, ShouldAutoSize, WithCustomStartCell, WithEvents
{
    private const HEADER_ROW = 6;

    private const FIRST_DATA_ROW = 8;

    /**
     * @param  Collection<int, Record>  $records
     * @param  Collection<string, ?string>  $vehicleTypes  Vehicle type keyed by plate.
     */
    public function __construct(
        protected Collection $records,
        protected Collection $vehicleTypes,
        protected string $companyLabel,
        protected string $periodLabel,
        protected ?string $unit,
    ) {}

    public function startCell(): string
    {
        return 'A'.self::FIRST_DATA_ROW;
    }

    public function collection()
    {
        return $this->records->values()->map(fn (Record $record, int $index): array => [
            $index + 1,
            Carbon::parse($record->date)->format('d-m-Y'),
            $this->vehicleTypes[$record->no_pol] ?? '-',
            $record->no_pol,
            $record->destination,
            (float) ($record->start_km ?? 0),
            (float) ($record->finish_km ?? 0),
            (float) ($record->totals ?? 0),
            (float) ($record->bbm ?? 0),
            (float) ($record->idr ?? 0),
            strtoupper($record->driver_1 ?: 'NONE').' - '.strtoupper($record->driver_2 ?: 'NONE'),
            $record->scale ?? '-',
        ]);
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'LAPORAN BAHAN BAKAR MINYAK (BBM)');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->setCellValue('A2', 'Perusahaan:');
                $sheet->setCellValue('C2', $this->companyLabel);
                $sheet->setCellValue('A3', 'Periode:');
                $sheet->setCellValue('C3', $this->periodLabel);
                $sheet->setCellValue('A4', 'No. Polisi:');
                $sheet->setCellValue('C4', $this->unit ?? 'Semua Unit');
                $sheet->getStyle('A2:A4')->getFont()->setBold(true);

                $header = self::HEADER_ROW;
                $sub = $header + 1;

                foreach (['A' => 'NO', 'B' => 'TANGGAL', 'C' => 'JENIS', 'D' => 'NOPOL', 'E' => 'TUJUAN',
                    'I' => 'LITER', 'J' => 'RUPIAH', 'K' => 'SUPIR', 'L' => 'SKALA'] as $column => $title) {
                    $sheet->setCellValue("{$column}{$header}", $title);
                    $sheet->mergeCells("{$column}{$header}:{$column}{$sub}");
                }

                $sheet->setCellValue("F{$header}", 'KILOMETER');
                $sheet->mergeCells("F{$header}:H{$header}");
                $sheet->setCellValue("F{$sub}", 'AWAL');
                $sheet->setCellValue("G{$sub}", 'AKHIR');
                $sheet->setCellValue("H{$sub}", 'TOTAL');

                $headerStyle = $sheet->getStyle("A{$header}:L{$sub}");
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getAlignment()->setHorizontal('center')->setVertical('center');
                $headerStyle->getFill()->setFillType('solid')->getStartColor()->setRGB('F2F2F2');
            },
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $first = self::FIRST_DATA_ROW;
                $last = $first + $this->records->count() - 1;
                $total = $last + 1;

                $sheet->setCellValue("A{$total}", 'JUMLAH');
                $sheet->mergeCells("A{$total}:G{$total}");

                // Plain values, not =SUM(): formulas are saved without a cached result,
                // so Protected View / previews / non-Excel viewers showed 0.
                $sheet->setCellValue("H{$total}", $this->records->sum(fn (Record $record) => (float) ($record->totals ?? 0)));
                $sheet->setCellValue("I{$total}", $this->records->sum(fn (Record $record) => (float) ($record->bbm ?? 0)));
                $sheet->setCellValue("J{$total}", $this->records->sum(fn (Record $record) => (float) ($record->idr ?? 0)));

                $sheet->getStyle("A{$total}:L{$total}")->getFont()->setBold(true);
                $sheet->getStyle("F{$first}:H{$total}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("J{$first}:J{$total}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("A{$first}:A{$last}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("L{$first}:L{$last}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A'.self::HEADER_ROW.":L{$total}")
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }
}
