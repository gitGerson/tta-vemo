<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Support\Facades\DB;
use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class MaintenancesYearExport implements FromView, ShouldAutoSize, WithEvents
{
    protected $year;

    public function __construct(int $year)
    {
        $this->year = $year;
    }

    public function view(): View
    {
        $mtc = DB::table('maintenances')
            ->select('no_pol', DB::raw('MONTH(date_out) as month'), DB::raw('SUM(total_amount) as total_amount'))
            ->whereYear('date_out', $this->year)
            ->groupBy('no_pol', 'month')
            ->get();

        $vehicles = Vehicle::all();

        return view('pages.report.maintenances.excel.year', [
            'mtc' => $mtc,
            'year' => $this->year,
            'vehicles' => $vehicles,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Total kolom: 2 (No, Kendaraan) + 12 bulan + 1 total = 15 kolom => A1:O1
                $event->sheet->mergeCells('A1:O1');

                $event->sheet->getStyle('A1')->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                ]);
            },
        ];
    }
}
