<?php

namespace App\Http\Controllers;

use App\DataTables\MaintenanceDailyReportDataTable;
use App\DataTables\MaintenanceYearReportDataTable;
use App\DataTables\RecordDailyReportDataTable;
use App\DataTables\RecordMonthlyReportDataTable;
use App\Models\Company;
use App\Models\Maintenance;
use App\Models\MaintenanceDetail;
use App\Models\Record;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
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

        return view('reports.record.daily', [
            'category' => $category,
            'table' => new RecordDailyReportDataTable($records, Vehicle::all(), $category),
            'company' => $this->selectableCompanies(),
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
        $vehicles = Vehicle::all();

        return view('reports.record.monthly', [
            'table' => new RecordMonthlyReportDataTable($records, $vehicles),
            'company' => $this->selectableCompanies(),
            'vehicles' => $vehicles,
            'date' => $dateString,
            'companyId' => $companyId,
        ]);
    }

    public function maintenancesDaily(Request $request)
    {
        $startDate = Carbon::parse($request->query('start_date', Carbon::now()->startOfMonth()->toDateString()))->toDateString();
        $endDate = Carbon::parse($request->query('end_date', Carbon::now()->toDateString()))->toDateString();
        $unitPlate = $request->query('unit');
        $companyId = $this->resolveCompanyId($request);

        $unit = $unitPlate
            ? Vehicle::where('plat_new', $unitPlate)
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->first()
            : null;

        $table = null;
        if ($unitPlate) {
            $table = new MaintenanceDailyReportDataTable($this->fetchMaintenanceDetails($unitPlate, $startDate, $endDate, $companyId));
        }

        return view('reports.maintenances.daily', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'unit' => $unit,
            'table' => $table,
            'vehicles' => Vehicle::all(),
            'companies' => $this->selectableCompanies(),
            'companyId' => $companyId,
        ]);
    }

    public function maintenancesMonthly(Request $request)
    {
        $dateString = $request->query('date', Carbon::now()->format('Y-m'));
        $month = Carbon::parse($dateString.'-01');
        $startDate = $month->copy()->startOfMonth()->toDateString();
        $endDate = $month->copy()->endOfMonth()->toDateString();
        $unitPlate = $request->query('unit');
        $companyId = $this->resolveCompanyId($request);

        $unit = $unitPlate
            ? Vehicle::where('plat_new', $unitPlate)
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->first()
            : null;

        $table = null;
        if ($unitPlate) {
            $table = new MaintenanceDailyReportDataTable($this->fetchMaintenanceDetails($unitPlate, $startDate, $endDate, $companyId));
        }

        return view('reports.maintenances.monthly', [
            'date' => $dateString,
            'unit' => $unit,
            'table' => $table,
            'vehicles' => Vehicle::all(),
            'companies' => $this->selectableCompanies(),
            'companyId' => $companyId,
        ]);
    }

    public function maintenancesYear(Request $request)
    {
        $year = $request->query('year', Carbon::now()->year);
        $companyId = $this->resolveCompanyId($request);

        [$mtc, $vehicles] = $this->fetchYearlyMaintenance($year, $companyId);

        return view('reports.maintenances.year', [
            'table' => new MaintenanceYearReportDataTable($mtc, $vehicles),
            'companies' => $this->selectableCompanies(),
            'companyId' => $companyId,
        ]);
    }

    public function dailyPdf(Request $request)
    {
        $category = strtoupper($request->query('category', 'KESELURUHAN'));
        $date = Carbon::parse($request->query('date', Carbon::now()->toDateString()))->toDateString();
        $companyId = $this->resolvePrintableCompanyId($request);

        if ($companyId === null) {
            return $this->missingPrintableCompanyResponse();
        }

        $records = Record::query()
            ->whereDate('date', $date)
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('date', 'desc')
            ->get();

        $vehicles = Vehicle::all();

        $pdf = Pdf::loadView('reports.pdf.daily', [
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
        $companyId = $this->resolvePrintableCompanyId($request);
        $unit = $request->query('unit');

        if ($companyId === null) {
            return $this->missingPrintableCompanyResponse();
        }

        $records = $this->fetchMonthlyRecords($date, $companyId, $unit);

        $pdf = Pdf::loadView('reports.pdf.monthly', [
            'records' => $records,
            'date' => $date,
            'unit' => $unit,
            'company' => Company::find($companyId),
            'vehicles' => Vehicle::all(),
        ])->setPaper('A4', 'portrait');

        return $pdf->download("laporan_bulanan_{$date->format('Y_m')}.pdf");
    }

    public function maintenancesDailyPdf(Request $request)
    {
        $companyId = $this->resolvePrintableCompanyId($request);
        $startDate = Carbon::parse($request->query('start_date', Carbon::now()->startOfMonth()->toDateString()))->toDateString();
        $endDate = Carbon::parse($request->query('end_date', Carbon::now()->toDateString()))->toDateString();
        $unitPlate = $request->query('unit');

        if ($companyId === null) {
            return $this->missingPrintableCompanyResponse();
        }

        $unit = $unitPlate
            ? Vehicle::where('plat_new', $unitPlate)
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->first()
            : null;

        $detailMtc = $unitPlate
            ? $this->fetchMaintenanceDetails($unitPlate, $startDate, $endDate, $companyId)
            : collect();

        $pdf = Pdf::loadView('reports.pdf.maintenances-daily', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'unit' => $unit,
            'detailMtc' => $detailMtc,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("laporan_pemeliharaan_{$unitPlate}_{$startDate}_{$endDate}.pdf");
    }

    public function maintenancesMonthlyPdf(Request $request)
    {
        $companyId = $this->resolvePrintableCompanyId($request);
        $dateString = $request->query('date', Carbon::now()->format('Y-m'));
        $month = Carbon::parse($dateString.'-01');
        $startDate = $month->copy()->startOfMonth()->toDateString();
        $endDate = $month->copy()->endOfMonth()->toDateString();
        $unitPlate = $request->query('unit');

        if ($companyId === null) {
            return $this->missingPrintableCompanyResponse();
        }

        $unit = $unitPlate
            ? Vehicle::where('plat_new', $unitPlate)
                ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
                ->first()
            : null;
        $detailMtc = $unitPlate ? $this->fetchMaintenanceDetails($unitPlate, $startDate, $endDate, $companyId) : collect();

        $pdf = Pdf::loadView('reports.pdf.maintenances-daily', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'unit' => $unit,
            'detailMtc' => $detailMtc,
        ])->setPaper('A4', 'portrait');

        return $pdf->download("laporan_pemeliharaan_bulanan_{$unitPlate}_{$month->format('Y_m')}.pdf");
    }

    public function maintenancesYearPdf(Request $request)
    {
        $year = (int) $request->query('year', Carbon::now()->year);
        $companyId = $this->resolvePrintableCompanyId($request);

        if ($companyId === null) {
            return $this->missingPrintableCompanyResponse();
        }

        [$mtc, $vehicles] = $this->fetchYearlyMaintenance($year, $companyId);

        $pdf = Pdf::loadView('reports.pdf.maintenances-year', [
            'mtc' => $mtc,
            'vehicles' => $vehicles,
            'year' => $year,
        ])->setPaper('A4', 'landscape');

        return $pdf->download("laporan_pemeliharaan_tahunan_{$year}.pdf");
    }

    public function maintenancesYearExcel(Request $request): StreamedResponse|RedirectResponse
    {
        $year = $request->query('year', Carbon::now()->year);
        $companyId = $this->resolvePrintableCompanyId($request);

        if ($companyId === null) {
            return $this->missingPrintableCompanyResponse();
        }

        [$mtc, $vehicles] = $this->fetchYearlyMaintenance($year, $companyId);

        $header = ['KENDARAAN', 'TAHUN MASUK'];
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

            $vehicle = $vehicles->where('plat_new', $noPol)->first();

            $vehicleLabel = $vehicle?->plat_new ?? $noPol;

            $row = [$vehicleLabel, $vehicle?->entry_year ?? '-'];
            for ($i = 1; $i <= 12; $i++) {
                $row[] = $sums[$i];
            }
            $row[] = $total;

            $rows[] = $row;
        }

        $totalRow = ['TOTAL', ''];
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
        if ($companyId === null || $companyId === '') {
            return null;
        }

        return (int) $companyId;
    }

    private function resolvePrintableCompanyId(Request $request): ?int
    {
        $companyId = $this->resolveCompanyId($request);

        if ($companyId === null) {
            return null;
        }

        return Company::query()->whereKey($companyId)->exists() ? $companyId : null;
    }

    private function missingPrintableCompanyResponse(): RedirectResponse
    {
        return back()
            ->withInput()
            ->withErrors(['company' => 'Pilih satu perusahaan untuk mencetak.']);
    }

    private function fetchMaintenanceDetails(string $plate, string $startDate, string $endDate, ?int $companyId = null): Collection
    {
        return MaintenanceDetail::query()
            ->select([
                'maintenances_details.id',
                'maintenances_details.maintenance_name',
                'maintenances_details.material',
                'maintenances_details.qty',
                'maintenances_details.price',
                'maintenances_details.subtotal',
                'maintenances.code_mt',
                'maintenances.date_in',
                'maintenances.date_out',
                'maintenances.mileage',
                'maintenances.status',
                'maintenances.description',
            ])
            ->join('maintenances', 'maintenances.id', '=', 'maintenances_details.maintenance_id')
            ->where('maintenances.no_pol', $plate)
            ->when($companyId, fn ($query) => $query->where('maintenances.company_id', $companyId))
            ->where('maintenances.status', Maintenance::STATUS_COMPLETED)
            ->whereBetween(DB::raw('COALESCE(maintenances.date_out, maintenances.date_in)'), [$startDate, $endDate])
            ->orderByRaw('COALESCE(maintenances.date_out, maintenances.date_in) ASC')
            ->orderBy('maintenances.code_mt')
            ->orderBy('maintenances_details.id')
            ->get();
    }

    private function fetchYearlyMaintenance(int $year, ?int $companyId = null): array
    {
        $mtc = Maintenance::query()
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->selectRaw('no_pol, MONTH(COALESCE(date_out, date_in)) as month, SUM(total_amount) as total_amount')
            ->whereYear(DB::raw('COALESCE(date_out, date_in)'), $year)
            ->groupBy('no_pol', 'month')
            ->orderBy('no_pol')
            ->get();

        $vehicles = Vehicle::all();

        return [$mtc, $vehicles];
    }

    /**
     * Companies the current user may pick from: their assigned companies, or
     * all companies when granted the tenant-bypass permission. The global
     * CompanyTenantScope already enforces this on the Company query.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Company>
     */
    private function selectableCompanies()
    {
        return Company::query()->orderBy('name')->get(['id', 'name', 'code']);
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
