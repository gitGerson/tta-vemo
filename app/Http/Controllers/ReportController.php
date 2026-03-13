<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Maintenances;
use App\Models\MaintenancesDetails;
use App\Models\Record;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function daily(Request $request)
    {
        $category = strtoupper($request->query('category', 'KESELURUHAN'));
        $date = Carbon::parse($request->query('date', Carbon::now()->toDateString()))->toDateString();
        $companyId = $this->resolveCompanyId($request);

        $records = Record::query()
            ->whereDate('date', $date)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('date', 'desc')
            ->get();

        return view('pages.report.record.daily', [
            'category' => $category,
            'record' => $records,
            'vehicle' => Vehicle::all(),
            'company' => Company::all(),
            'defaluteCompany' => $this->defaultCompany(),
            'QueryCompany' => $companyId,
        ]);
    }

    public function monthly(Request $request)
    {
        $dateString = $request->query('date', Carbon::now()->format('Y-m'));
        $date = Carbon::parse($dateString.'-01');
        $companyId = $this->resolveCompanyId($request);
        $unit = $request->query('unit');

        $records = $this->fetchMonthlyRecords($date, $companyId, $unit);

        return view('pages.report.record.monthly', [
            'record' => $records,
            'company' => Company::all(),
            'vehicles' => Vehicle::all(),
            'date' => $dateString,
            'defaluteCompany' => $this->defaultCompany(),
        ]);
    }

    public function maintenancesDaily(Request $request)
    {
        $startDate = Carbon::parse($request->query('start_date', Carbon::now()->startOfMonth()->toDateString()))->toDateString();
        $endDate = Carbon::parse($request->query('end_date', Carbon::now()->toDateString()))->toDateString();
        $unitPlate = $request->query('unit');

        $unit = $unitPlate
            ? Vehicle::where('plat_new', $unitPlate)->orWhere('plat_old', $unitPlate)->first()
            : null;

        $detailMtc = collect();
        if ($unitPlate) {
            $detailMtc = $this->fetchMaintenanceDetails($unitPlate, $startDate, $endDate);
        }

        return view('pages.report.maintenances.daily', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'unit' => $unit,
            'detailMtc' => $detailMtc,
            'vehicles' => Vehicle::all(),
        ]);
    }

    public function maintenancesYear(Request $request)
    {
        $year = $request->query('year', Carbon::now()->year);

        [$mtc, $vehicles] = $this->fetchYearlyMaintenance($year);

        return view('pages.report.maintenances.year', [
            'mtc' => $mtc,
            'vehicles' => $vehicles,
        ]);
    }

    public function dailyPdf(Request $request)
    {
        $category = strtoupper($request->query('category', 'KESELURUHAN'));
        $date = Carbon::parse($request->query('date', Carbon::now()->toDateString()))->toDateString();
        $companyId = $this->resolveCompanyId($request);

        $records = Record::query()
            ->whereDate('date', $date)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('date', 'desc')
            ->get();

        $vehicles = Vehicle::all();

        $pdf = Pdf::loadView('pages.report.pdf.daily', [
            'records' => $records,
            'category' => $category,
            'date' => $date,
            'vehicles' => $vehicles,
            'company' => Company::find($companyId),
        ])->setPaper('A4', 'portrait');

        return $pdf->download("laporan_harian_{$date}.pdf");
    }

    public function monthlyPdf(Request $request)
    {
        $dateString = $request->query('date', Carbon::now()->format('Y-m'));
        $date = Carbon::parse($dateString.'-01');
        $companyId = $this->resolveCompanyId($request);
        $unit = $request->query('unit');

        $records = $this->fetchMonthlyRecords($date, $companyId, $unit);

        $pdf = Pdf::loadView('pages.report.pdf.monthly', [
            'records' => $records,
            'date' => $date,
            'unit' => $unit,
            'company' => Company::find($companyId),
        ])->setPaper('A4', 'landscape');

        return $pdf->download("laporan_bulanan_{$date->format('Y_m')}.pdf");
    }

    public function maintenancesDailyPdf(Request $request)
    {
        $startDate = Carbon::parse($request->query('start_date', Carbon::now()->startOfMonth()->toDateString()))->toDateString();
        $endDate = Carbon::parse($request->query('end_date', Carbon::now()->toDateString()))->toDateString();
        $unitPlate = $request->query('unit');

        $unit = $unitPlate
            ? Vehicle::where('plat_new', $unitPlate)->orWhere('plat_old', $unitPlate)->first()
            : null;

        $detailMtc = $unitPlate
            ? $this->fetchMaintenanceDetails($unitPlate, $startDate, $endDate)
            : collect();

        $pdf = Pdf::loadView('pages.report.pdf.maintenances-daily', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'unit' => $unit,
            'detailMtc' => $detailMtc,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("laporan_pemeliharaan_{$unitPlate}_{$startDate}_{$endDate}.pdf");
    }

    public function maintenancesYearPdf(Request $request)
    {
        $year = (int) $request->query('year', Carbon::now()->year);
        [$mtc, $vehicles] = $this->fetchYearlyMaintenance($year);

        $pdf = Pdf::loadView('pages.report.pdf.maintenances-year', [
            'mtc' => $mtc,
            'vehicles' => $vehicles,
            'year' => $year,
        ])->setPaper('A4', 'landscape');

        return $pdf->download("laporan_pemeliharaan_tahunan_{$year}.pdf");
    }

    public function maintenancesYearExcel(Request $request): StreamedResponse
    {
        $year = $request->query('year', Carbon::now()->year);
        [$mtc, $vehicles] = $this->fetchYearlyMaintenance($year);

        $header = ['KENDARAAN'];
        foreach (['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'] as $label) {
            $header[] = $label;
        }
        $header[] = 'TOTAL';

        $grouped = $mtc->groupBy('no_pol');
        $monthlyTotals = array_fill(1, 12, 0);
        $rows = [$header];

        foreach ($grouped as $noPol => $items) {
            $sums = array_fill(1, 12, 0);
            $total = 0;

            foreach ($items as $item) {
                $sums[$item->month] += $item->total_amount;
                $monthlyTotals[$item->month] += $item->total_amount;
                $total += $item->total_amount;
            }

            $vehicleLabel = optional($vehicles->where('plat_new', $noPol)->first())->plat_new
                ?? optional($vehicles->where('plat_old', $noPol)->first())->plat_old
                ?? $noPol;

            $row = [$vehicleLabel];
            for ($i = 1; $i <= 12; $i++) {
                $row[] = $sums[$i];
            }
            $row[] = $total;

            $rows[] = $row;
        }

        $totalRow = ['TOTAL'];
        $overall = 0;
        for ($i = 1; $i <= 12; $i++) {
            $totalRow[] = $monthlyTotals[$i];
            $overall += $monthlyTotals[$i];
        }
        $totalRow[] = $overall;
        $rows[] = $totalRow;

        $callback = function () use ($rows) {
            $fh = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($fh, $row);
            }
            fclose($fh);
        };

        $filename = "laporan_pemeliharaan_tahunan_{$year}.csv";

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function resolveCompanyId(Request $request): ?int
    {
        $companyId = $request->query('company');
        if ($companyId) {
            return (int) $companyId;
        }

        return $this->defaultCompany()?->id;
    }

    private function defaultCompany(): ?Company
    {
        return Company::where('code', 'TTI')->first() ?? Company::first();
    }

    private function fetchMaintenanceDetails(string $plate, string $startDate, string $endDate): Collection
    {
        return MaintenancesDetails::query()
            ->select([
                'maintenances_details.maintenance_name',
                DB::raw('SUM(COALESCE(NULLIF(maintenances_details.qty, \'\'), 0)) as total_qty'),
                DB::raw('SUM(maintenances_details.price) as total_price'),
            ])
            ->join('maintenances', 'maintenances.id', '=', 'maintenances_details.maintenance_id')
            ->where('maintenances.no_pol', $plate)
            ->whereBetween(DB::raw('COALESCE(maintenances.date_out, maintenances.date_in)'), [$startDate, $endDate])
            ->groupBy('maintenances_details.maintenance_name')
            ->orderBy('maintenances_details.maintenance_name')
            ->get();
    }

    private function fetchYearlyMaintenance(int $year): array
    {
        $mtc = Maintenances::query()
            ->selectRaw('no_pol, MONTH(COALESCE(date_out, date_in)) as month, SUM(total_amount) as total_amount')
            ->whereYear(DB::raw('COALESCE(date_out, date_in)'), $year)
            ->groupBy('no_pol', 'month')
            ->orderBy('no_pol')
            ->get();

        $vehicles = Vehicle::all();

        return [$mtc, $vehicles];
    }

    private function fetchMonthlyRecords(Carbon $date, ?int $companyId, ?string $unit): Collection
    {
        return Record::query()
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->when($unit, fn ($query) => $query->where('no_pol', $unit))
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('date')
            ->get();
    }
}
