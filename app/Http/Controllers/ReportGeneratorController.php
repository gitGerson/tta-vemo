<?php

namespace App\Http\Controllers;

use App\DataTables\RecordReportGeneratorDataTable;
use App\Exports\RecordReportGeneratorExport;
use App\Models\Company;
use App\Models\Record;
use App\Models\Vehicle;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Flexible fuel (BBM) record report: same table as the monthly report, filtered
 * by any number of companies, a day or month range, and a single vehicle or all
 * vehicles. Exports to Excel (DomPDF ran out of memory on long ranges).
 */
class ReportGeneratorController extends Controller
{
    public const TYPE_DAY = 'day';

    public const TYPE_MONTH = 'month';

    public function index(Request $request)
    {
        $companies = $this->selectableCompanies();
        $filters = $this->resolveFilters($request, $companies);
        $vehicles = Vehicle::all();

        return view('reports.record.generator', [
            'table' => new RecordReportGeneratorDataTable($this->fetchRecords($filters), $vehicles),
            'company' => $companies,
            'vehicles' => $vehicles,
            'filters' => $filters,
        ]);
    }

    public function excel(Request $request)
    {
        $companies = $this->selectableCompanies();
        $filters = $this->resolveFilters($request, $companies);
        $selectedCompanies = $companies->whereIn('id', $filters['companies']);

        $isDay = $filters['type'] === self::TYPE_DAY;
        $periodFormat = $isDay ? 'd F Y' : 'F Y';
        $periodFrom = $filters['from']->copy()->locale('id')->translatedFormat($periodFormat);
        $periodTo = $filters['to']->copy()->locale('id')->translatedFormat($periodFormat);

        $export = new RecordReportGeneratorExport(
            $this->fetchRecords($filters),
            Vehicle::query()->whereNotNull('plat_new')->pluck('type', 'plat_new'),
            $selectedCompanies->isEmpty()
                ? 'Semua Perusahaan'
                : $selectedCompanies->map(fn (Company $company) => $company->name ?: $company->code)->implode(', '),
            $periodFrom === $periodTo ? $periodFrom : "{$periodFrom} - {$periodTo}",
            $filters['unit'],
        );

        $format = $isDay ? 'Y_m_d' : 'Y_m';

        return Excel::download($export, sprintf(
            'laporan_bbm_%s_%s.xlsx',
            $filters['from']->format($format),
            $filters['to']->format($format),
        ));
    }

    /**
     * Normalize the query string into a filter set. Unknown / inaccessible
     * company ids are dropped (empty = all accessible companies), an empty
     * unit means all vehicles, and a reversed range is swapped.
     *
     * @param  Collection<int, Company>  $companies
     * @return array{type: string, companies: list<int>, unit: ?string, start: string, end: string, from: Carbon, to: Carbon}
     */
    private function resolveFilters(Request $request, Collection $companies): array
    {
        $type = $request->query('type') === self::TYPE_DAY ? self::TYPE_DAY : self::TYPE_MONTH;

        $companyIds = collect((array) $request->query('companies', []))
            ->filter(fn ($id) => is_scalar($id) && ctype_digit((string) $id))
            ->map(fn ($id) => (int) $id)
            ->intersect($companies->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $unit = $request->query('unit');
        $unit = is_string($unit) && $unit !== '' ? $unit : null;

        if ($type === self::TYPE_DAY) {
            $from = $this->parseDate($request->query('start'), '!Y-m-d') ?? Carbon::now()->startOfMonth();
            $to = $this->parseDate($request->query('end'), '!Y-m-d') ?? Carbon::now()->startOfDay();
        } else {
            $from = $this->parseDate($request->query('start'), '!Y-m') ?? Carbon::now()->startOfMonth();
            $to = $this->parseDate($request->query('end'), '!Y-m') ?? Carbon::now()->startOfMonth();
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        if ($type === self::TYPE_MONTH) {
            $from = $from->copy()->startOfMonth();
            $to = $to->copy()->endOfMonth()->startOfDay();
        }

        $inputFormat = $type === self::TYPE_DAY ? 'Y-m-d' : 'Y-m';

        return [
            'type' => $type,
            'companies' => $companyIds,
            'unit' => $unit,
            'start' => $from->format($inputFormat),
            'end' => $to->format($inputFormat),
            'from' => $from,
            'to' => $to,
        ];
    }

    private function parseDate(mixed $value, string $format): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat($format, $value);
        } catch (InvalidFormatException) {
            return null;
        }

        return $date instanceof Carbon && $date->format(ltrim($format, '!')) === $value ? $date : null;
    }

    /**
     * @param  array{companies: list<int>, unit: ?string, from: Carbon, to: Carbon}  $filters
     * @return Collection<int, Record>
     */
    private function fetchRecords(array $filters): Collection
    {
        return Record::query()
            ->whereDate('date', '>=', $filters['from']->toDateString())
            ->whereDate('date', '<=', $filters['to']->toDateString())
            ->when($filters['companies'] !== [], fn ($query) => $query->whereIn('company_id', $filters['companies']))
            ->when($filters['unit'], fn ($query, $unit) => $query->where('no_pol', $unit))
            ->orderBy('date')
            ->orderBy('no_pol')
            ->get();
    }

    /**
     * Companies the current user may pick from; the global CompanyTenantScope
     * limits this to the user's assigned companies unless they can bypass it.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Company>
     */
    private function selectableCompanies()
    {
        return Company::query()->orderBy('name')->get(['id', 'name', 'code']);
    }
}
