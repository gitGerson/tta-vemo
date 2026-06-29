<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Services\DashboardMetricsService;
use App\Services\ServiceReminderService;
use App\Services\VehicleInsuranceMonitorService;
use App\Services\VehiclePhotoMonitorService;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HomeController extends Controller
{
    /**
     * Maximum number of rows rendered in each dashboard list card. Header counts
     * still report the true total; only the visible list is capped.
     */
    private const MAX_LIST_ITEMS = 5;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(Request $request, ServiceReminderService $serviceReminders, VehiclePhotoMonitorService $photoMonitor, VehicleInsuranceMonitorService $insuranceMonitor, DashboardMetricsService $metrics)
    {
        $data = $this->buildDashboardData($request, $serviceReminders, $photoMonitor, $insuranceMonitor, $metrics);

        // The filter form posts via AJAX (X-Requested-With) to refresh in place;
        // return only the content partial so the page is not fully reloaded.
        if ($request->ajax()) {
            return view('home.partials.content', $data);
        }

        return view('home', $data);
    }

    /**
     * Build the full dashboard view-data array for the given filter request.
     *
     * @return array<string, mixed>
     */
    private function buildDashboardData(Request $request, ServiceReminderService $serviceReminders, VehiclePhotoMonitorService $photoMonitor, VehicleInsuranceMonitorService $insuranceMonitor, DashboardMetricsService $metrics): array
    {
        $user = auth()->user();
        $filters = $this->resolveDashboardFilters($request);
        $selectedCompanyId = $filters['company_id'];
        $selectedMonth = $filters['month'];

        $activePlates = Vehicle::where('status', 'ACTIVE')
            ->when($selectedCompanyId, fn ($query) => $query->where('company_id', $selectedCompanyId))
            ->pluck('plat_new');
        $passive = Vehicle::where('status', 'PASSIVE')
            ->when($selectedCompanyId, fn ($query) => $query->where('company_id', $selectedCompanyId))
            ->count();

        $draftPlates = Maintenance::where('status', Maintenance::STATUS_DRAFT)
            ->whereIn('no_pol', $activePlates)
            ->when($selectedCompanyId, fn ($query) => $query->where('company_id', $selectedCompanyId))
            ->distinct()
            ->pluck('no_pol');

        $awaitingPlates = Maintenance::where('status', Maintenance::STATUS_COMPLETED)
            ->where('is_confirmed', false)
            ->whereIn('no_pol', $activePlates)
            ->whereNotIn('no_pol', $draftPlates)
            ->when($selectedCompanyId, fn ($query) => $query->where('company_id', $selectedCompanyId))
            ->distinct()
            ->pluck('no_pol');

        $underMaintenance = $draftPlates->count();
        $awaiting = $awaitingPlates->count();
        $operational = max(0, $activePlates->count() - $underMaintenance - $awaiting);

        $conditionSummary = [
            'total' => $activePlates->count() + $passive,
            'active' => $activePlates->count(),
            'items' => [
                [
                    'label' => 'Operasional',
                    'sublabel' => 'Aktif, tidak ada pekerjaan terbuka',
                    'count' => $operational,
                    'color' => 'success',
                    'hex' => '#71dd37',
                    'icon' => 'bx-check-shield',
                    'link' => route('vehicles.index'),
                ],
                [
                    'label' => 'Dalam Pemeliharaan',
                    'sublabel' => 'Servis sedang berjalan',
                    'count' => $underMaintenance,
                    'color' => 'warning',
                    'hex' => '#ffab00',
                    'icon' => 'bx-wrench',
                    'link' => route('maintenances.index'),
                ],
                [
                    'label' => 'Menunggu Konfirmasi',
                    'sublabel' => 'Selesai, perlu ditinjau',
                    'count' => $awaiting,
                    'color' => 'info',
                    'hex' => '#03c3ec',
                    'icon' => 'bx-time-five',
                    'link' => route('maintenances.index'),
                ],
                [
                    'label' => 'Tidak Aktif',
                    'sublabel' => 'Diparkir / tidak digunakan',
                    'count' => $passive,
                    'color' => 'secondary',
                    'hex' => '#8592a3',
                    'icon' => 'bx-pause-circle',
                    'link' => route('vehicles.index'),
                ],
            ],
        ];

        $plates = Vehicle::query()
            ->when($selectedCompanyId, fn ($query) => $query->where('company_id', $selectedCompanyId))
            ->pluck('plat_new');

        $recentMaintenance = Maintenance::whereIn('no_pol', $plates)
            ->when($selectedCompanyId, fn ($query) => $query->where('company_id', $selectedCompanyId))
            ->where(function ($query) use ($selectedMonth): void {
                $query->whereBetween('date_out', [$selectedMonth->toDateString(), $selectedMonth->copy()->endOfMonth()->toDateString()])
                    ->orWhere(function ($query) use ($selectedMonth): void {
                        $query->whereNull('date_out')
                            ->whereBetween('date_in', [$selectedMonth->toDateString(), $selectedMonth->copy()->endOfMonth()->toDateString()]);
                    });
            })
            ->latest('updated_at')
            ->limit(self::MAX_LIST_ITEMS)
            ->get()
            ->map(function (Maintenance $maintenance): array {
                $state = $this->classifyMaintenance($maintenance);
                $date = $maintenance->date_out ?? $maintenance->date_in;
                $type = $maintenance->isServiceRoutine ? 'Servis Rutin' : 'Non-rutin';

                return [
                    'plate' => $maintenance->no_pol,
                    'meta' => $type.' · '.($date?->format('d M Y') ?? $maintenance->code_mt),
                    'badge' => $state['label'],
                    'color' => $state['color'],
                    'icon' => $state['icon'],
                    'link' => route('maintenances.show', $maintenance),
                ];
            })
            ->all();

        // Gated by a dedicated permission so the reminder audience can be defined
        // independently. dueVehicles() is company-scoped via the Vehicle
        // CompanyTenantScope (bypassed by management.tenant.bypass).
        $canRemind = $user?->hasPermission('operational.maintenances.reminders') === true;
        $due = $canRemind ? $serviceReminders->dueVehicles($selectedCompanyId) : collect();

        // Monthly photo reminders, gated by a dedicated permission and company-scoped via
        // the Vehicle CompanyTenantScope (bypassed by management.tenant.bypass).
        $canPhotoRemind = $user?->hasPermission('operational.vehicle_photos.reminders') === true;
        $missingPhotos = $canPhotoRemind ? $photoMonitor->incompleteVehicles($selectedMonth, $selectedCompanyId) : collect();

        // Insurance expiry reminders (H-30/14/7/3), gated by a dedicated permission and
        // company-scoped via the VehicleInsurance CompanyTenantScope.
        $canInsuranceRemind = $user?->hasPermission('master.vehicle_insurances.reminders') === true;
        // Overdue (lapsed) policies first, then those approaching expiry.
        $insurance = $canInsuranceRemind
            ? $insuranceMonitor->overduePolicies($selectedMonth, $selectedCompanyId)->concat($insuranceMonitor->expiringPolicies($selectedMonth, $selectedCompanyId))
            : collect();

        // At-a-glance KPI cards. Actionable reminder counts are shown first (when the
        // user may see them), padded with always-available fleet stats so the row never
        // looks sparse. The top 4 are rendered on the dashboard.
        $kpis = collect([
            ['visible' => $canRemind, 'label' => 'Tenggat Waktu Servis', 'value' => $due->count(), 'sub' => 'melewati batas servis', 'icon' => 'bx-wrench', 'color' => 'warning', 'link' => route('maintenances.index')],
            ['visible' => $canInsuranceRemind, 'label' => 'Perhatian Asuransi', 'value' => $insurance->count(), 'sub' => 'akan berakhir atau terlambat', 'icon' => 'bx-shield-quarter', 'color' => 'danger', 'link' => route('vehicle-insurances.index')],
            ['visible' => $canPhotoRemind, 'label' => 'Foto Belum Lengkap', 'value' => $missingPhotos->count(), 'sub' => 'belum lengkap bulan ini', 'icon' => 'bx-camera', 'color' => 'info', 'link' => route('vehicles.index')],
            ['visible' => true, 'label' => 'Total Kendaraan', 'value' => $conditionSummary['total'], 'sub' => $conditionSummary['active'].' aktif', 'icon' => 'bx-car', 'color' => 'primary', 'link' => route('vehicles.index')],
            ['visible' => true, 'label' => 'Dalam Pemeliharaan', 'value' => $underMaintenance, 'sub' => 'servis sedang berjalan', 'icon' => 'bx-cog', 'color' => 'warning', 'link' => route('maintenances.index')],
            ['visible' => true, 'label' => 'Menunggu Konfirmasi', 'value' => $awaiting, 'sub' => 'selesai, perlu ditinjau', 'icon' => 'bx-time-five', 'color' => 'info', 'link' => route('maintenances.index')],
        ])->filter(fn (array $kpi): bool => $kpi['visible'])->take(4)->values()->all();

        // Quick-action buttons, gated so each only appears when the user can use it.
        $quickActions = collect([
            ['label' => 'Catat Servis', 'icon' => 'bx-wrench', 'route' => 'maintenances.create', 'perm' => 'operational.maintenances.create'],
            ['label' => 'Scan QR', 'icon' => 'bx-qr-scan', 'route' => 'qr-scan.index', 'perm' => 'operational.qr_scan.view'],
            ['label' => 'Tambah Kendaraan', 'icon' => 'bx-car', 'route' => 'vehicles.create', 'perm' => 'master.vehicles.create'],
            ['label' => 'Semua Kendaraan', 'icon' => 'bx-list-ul', 'route' => 'vehicles.index', 'perm' => 'master.vehicles.view'],
        ])->filter(fn (array $action): bool => $user?->hasPermission($action['perm']) === true)
            ->map(fn (array $action): array => ['label' => $action['label'], 'icon' => $action['icon'], 'url' => route($action['route'])])
            ->values()->all();

        // Financial summary widgets are gated so cost data is only shown to roles
        // granted the dedicated permission; utilization/composition stay ungated.
        $canViewFinancials = $user?->hasPermission('dashboard.financials.view') === true;

        return [
            'dashboardContext' => [
                'company' => $this->dashboardCompanyLabel($filters['company_options'], $selectedCompanyId),
                'company_options' => $filters['company_options'],
                'selected_company_id' => $selectedCompanyId,
                'selected_month' => $selectedMonth->format('Y-m'),
                'month' => $selectedMonth->locale('id')->translatedFormat('F Y'),
            ],
            'conditionSummary' => $conditionSummary,
            'recentMaintenance' => $recentMaintenance,
            'kpis' => $kpis,
            'quickActions' => $quickActions,
            'serviceReminders' => ['visible' => $canRemind, 'count' => $due->count(), 'items' => $due->take(self::MAX_LIST_ITEMS)->all()],
            'photoReminders' => ['visible' => $canPhotoRemind, 'count' => $missingPhotos->count(), 'items' => $missingPhotos->take(self::MAX_LIST_ITEMS)->all()],
            'insuranceReminders' => ['visible' => $canInsuranceRemind, 'count' => $insurance->count(), 'items' => $insurance->take(self::MAX_LIST_ITEMS)->all()],
            'financials' => [
                'visible' => $canViewFinancials,
                'summary' => $canViewFinancials ? $metrics->financialSummary(month: $selectedMonth, companyId: $selectedCompanyId) : null,
                'trend' => $canViewFinancials ? $metrics->spendTrend(month: $selectedMonth, companyId: $selectedCompanyId) : null,
            ],
            'utilization' => $metrics->utilizationSummary($selectedMonth, $selectedCompanyId),
            'fleetComposition' => $metrics->fleetComposition($selectedCompanyId),
            'maintenanceVolume' => $metrics->maintenanceVolumeTrend(month: $selectedMonth, companyId: $selectedCompanyId),
        ];
    }

    /**
     * Derive an operational status for a maintenance record based on its most
     * recent state: done, under maintenance, or needs follow-up.
     *
     * @return array{label: string, color: string, icon: string}
     */
    private function classifyMaintenance(Maintenance $maintenance): array
    {
        if ($maintenance->status === Maintenance::STATUS_CANCELLED) {
            return ['label' => 'Dibatalkan', 'color' => 'danger', 'icon' => 'bx-x-circle'];
        }

        if ($maintenance->status === Maintenance::STATUS_COMPLETED) {
            return $maintenance->is_confirmed
                ? ['label' => 'Selesai', 'color' => 'success', 'icon' => 'bx-check-circle']
                : ['label' => 'Perlu Tindak Lanjut', 'color' => 'warning', 'icon' => 'bx-error-circle'];
        }

        // DRAFT: under maintenance, but flag as follow-up once it has gone stale.
        $stale = $maintenance->updated_at !== null
            && $maintenance->updated_at->lt(now()->subDays(7));

        return $stale
            ? ['label' => 'Perlu Tindak Lanjut', 'color' => 'warning', 'icon' => 'bx-error-circle']
            : ['label' => 'Dalam Pemeliharaan', 'color' => 'info', 'icon' => 'bx-wrench'];
    }

    /**
     * @return array{company_options: Collection<int, Company>, company_id: ?int, month: Carbon}
     */
    private function resolveDashboardFilters(Request $request): array
    {
        $companyOptions = Company::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $requestedCompanyId = (int) $request->query('company', 0);
        $selectedCompanyId = $companyOptions->contains('id', $requestedCompanyId) ? $requestedCompanyId : null;

        if ($selectedCompanyId === null && $companyOptions->count() === 1) {
            $selectedCompanyId = (int) $companyOptions->first()->id;
        }

        $month = $this->parseDashboardMonth($request->query('month'));

        return [
            'company_options' => $companyOptions,
            'company_id' => $selectedCompanyId,
            'month' => $month,
        ];
    }

    private function parseDashboardMonth(mixed $value): Carbon
    {
        if (
            is_string($value)
            && preg_match('/^\d{4}-\d{2}$/', $value) === 1
            && Carbon::canBeCreatedFromFormat($value.'-01', 'Y-m-d')
        ) {
            return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfMonth();
        }

        return now()->startOfMonth();
    }

    /**
     * @param  Collection<int, Company>  $companyOptions
     */
    private function dashboardCompanyLabel(Collection $companyOptions, ?int $selectedCompanyId): string
    {
        if ($selectedCompanyId !== null) {
            return $companyOptions->firstWhere('id', $selectedCompanyId)?->name ?? '-';
        }

        return 'Semua Perusahaan';
    }
}
