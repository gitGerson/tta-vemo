<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Services\ServiceReminderService;
use App\Services\VehicleInsuranceMonitorService;
use App\Services\VehiclePhotoMonitorService;
use Illuminate\Contracts\Support\Renderable;

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
    public function index(ServiceReminderService $serviceReminders, VehiclePhotoMonitorService $photoMonitor, VehicleInsuranceMonitorService $insuranceMonitor)
    {
        $activePlates = Vehicle::where('status', 'ACTIVE')->pluck('plat_new');
        $passive = Vehicle::where('status', 'PASSIVE')->count();

        $draftPlates = Maintenance::where('status', Maintenance::STATUS_DRAFT)
            ->whereIn('no_pol', $activePlates)
            ->distinct()
            ->pluck('no_pol');

        $awaitingPlates = Maintenance::where('status', Maintenance::STATUS_COMPLETED)
            ->where('is_confirmed', false)
            ->whereIn('no_pol', $activePlates)
            ->whereNotIn('no_pol', $draftPlates)
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
                    'label' => 'Operational',
                    'sublabel' => 'Active, no open work',
                    'count' => $operational,
                    'color' => 'success',
                    'hex' => '#71dd37',
                    'icon' => 'bx-check-shield',
                    'link' => route('vehicles.index'),
                ],
                [
                    'label' => 'Under Maintenance',
                    'sublabel' => 'Service in progress',
                    'count' => $underMaintenance,
                    'color' => 'warning',
                    'hex' => '#ffab00',
                    'icon' => 'bx-wrench',
                    'link' => route('maintenances.index'),
                ],
                [
                    'label' => 'Awaiting Confirmation',
                    'sublabel' => 'Completed, needs review',
                    'count' => $awaiting,
                    'color' => 'info',
                    'hex' => '#03c3ec',
                    'icon' => 'bx-time-five',
                    'link' => route('maintenances.index'),
                ],
                [
                    'label' => 'Inactive',
                    'sublabel' => 'Parked / retired',
                    'count' => $passive,
                    'color' => 'secondary',
                    'hex' => '#8592a3',
                    'icon' => 'bx-pause-circle',
                    'link' => route('vehicles.index'),
                ],
            ],
        ];

        $plates = Vehicle::pluck('plat_new');

        $recentMaintenance = Maintenance::whereIn('no_pol', $plates)
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
        $canRemind = auth()->user()?->hasPermission('operational.maintenances.reminders') === true;
        $due = $canRemind ? $serviceReminders->dueVehicles() : collect();

        // Monthly photo reminders, gated by a dedicated permission and company-scoped via
        // the Vehicle CompanyTenantScope (bypassed by management.tenant.bypass).
        $canPhotoRemind = auth()->user()?->hasPermission('operational.vehicle_photos.reminders') === true;
        $missingPhotos = $canPhotoRemind ? $photoMonitor->incompleteVehicles() : collect();

        // Insurance expiry reminders (H-30/14/7/3), gated by a dedicated permission and
        // company-scoped via the VehicleInsurance CompanyTenantScope.
        $canInsuranceRemind = auth()->user()?->hasPermission('master.vehicle_insurances.reminders') === true;
        // Overdue (lapsed) policies first, then those approaching expiry.
        $insurance = $canInsuranceRemind
            ? $insuranceMonitor->overduePolicies()->concat($insuranceMonitor->expiringPolicies())
            : collect();

        // At-a-glance KPI cards. Actionable reminder counts are shown first (when the
        // user may see them), padded with always-available fleet stats so the row never
        // looks sparse. The top 4 are rendered on the dashboard.
        $kpis = collect([
            ['visible' => $canRemind, 'label' => 'Due for Service', 'value' => $due->count(), 'sub' => 'past service threshold', 'icon' => 'bx-wrench', 'color' => 'warning', 'link' => route('maintenances.index')],
            ['visible' => $canInsuranceRemind, 'label' => 'Insurance Attention', 'value' => $insurance->count(), 'sub' => 'expiring or overdue', 'icon' => 'bx-shield-quarter', 'color' => 'danger', 'link' => route('vehicle-insurances.index')],
            ['visible' => $canPhotoRemind, 'label' => 'Photos Incomplete', 'value' => $missingPhotos->count(), 'sub' => 'missing this month', 'icon' => 'bx-camera', 'color' => 'info', 'link' => route('vehicles.index')],
            ['visible' => true, 'label' => 'Total Vehicles', 'value' => $conditionSummary['total'], 'sub' => $conditionSummary['active'].' active', 'icon' => 'bx-car', 'color' => 'primary', 'link' => route('vehicles.index')],
            ['visible' => true, 'label' => 'Under Maintenance', 'value' => $underMaintenance, 'sub' => 'service in progress', 'icon' => 'bx-cog', 'color' => 'warning', 'link' => route('maintenances.index')],
            ['visible' => true, 'label' => 'Awaiting Confirmation', 'value' => $awaiting, 'sub' => 'completed, needs review', 'icon' => 'bx-time-five', 'color' => 'info', 'link' => route('maintenances.index')],
        ])->filter(fn (array $kpi): bool => $kpi['visible'])->take(4)->values()->all();

        // Quick-action buttons, gated so each only appears when the user can use it.
        $user = auth()->user();
        $quickActions = collect([
            ['label' => 'Log Service', 'icon' => 'bx-wrench', 'route' => 'maintenances.create', 'perm' => 'operational.maintenances.create'],
            ['label' => 'Scan QR', 'icon' => 'bx-qr-scan', 'route' => 'qr-scan.index', 'perm' => 'operational.qr_scan.view'],
            ['label' => 'Add Vehicle', 'icon' => 'bx-car', 'route' => 'vehicles.create', 'perm' => 'master.vehicles.create'],
            ['label' => 'All Vehicles', 'icon' => 'bx-list-ul', 'route' => 'vehicles.index', 'perm' => 'master.vehicles.view'],
        ])->filter(fn (array $action): bool => $user?->hasPermission($action['perm']) === true)
            ->map(fn (array $action): array => ['label' => $action['label'], 'icon' => $action['icon'], 'url' => route($action['route'])])
            ->values()->all();

        return view('home', [
            'conditionSummary' => $conditionSummary,
            'recentMaintenance' => $recentMaintenance,
            'kpis' => $kpis,
            'quickActions' => $quickActions,
            'serviceReminders' => ['visible' => $canRemind, 'count' => $due->count(), 'items' => $due->take(self::MAX_LIST_ITEMS)->all()],
            'photoReminders' => ['visible' => $canPhotoRemind, 'count' => $missingPhotos->count(), 'items' => $missingPhotos->take(self::MAX_LIST_ITEMS)->all()],
            'insuranceReminders' => ['visible' => $canInsuranceRemind, 'count' => $insurance->count(), 'items' => $insurance->take(self::MAX_LIST_ITEMS)->all()],
        ]);
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
            return ['label' => 'Cancelled', 'color' => 'danger', 'icon' => 'bx-x-circle'];
        }

        if ($maintenance->status === Maintenance::STATUS_COMPLETED) {
            return $maintenance->is_confirmed
                ? ['label' => 'Done', 'color' => 'success', 'icon' => 'bx-check-circle']
                : ['label' => 'Needs Follow-up', 'color' => 'warning', 'icon' => 'bx-error-circle'];
        }

        // DRAFT: under maintenance, but flag as follow-up once it has gone stale.
        $stale = $maintenance->updated_at !== null
            && $maintenance->updated_at->lt(now()->subDays(7));

        return $stale
            ? ['label' => 'Needs Follow-up', 'color' => 'warning', 'icon' => 'bx-error-circle']
            : ['label' => 'Under Maintenance', 'color' => 'info', 'icon' => 'bx-wrench'];
    }
}
