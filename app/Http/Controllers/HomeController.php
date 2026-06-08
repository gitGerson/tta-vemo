<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Vehicle;
use App\Services\ServiceReminderService;
use Illuminate\Contracts\Support\Renderable;

class HomeController extends Controller
{
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
    public function index(ServiceReminderService $serviceReminders)
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
            ->limit(7)
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

        $due = $serviceReminders->dueVehicles();

        return view('home', [
            'conditionSummary' => $conditionSummary,
            'recentMaintenance' => $recentMaintenance,
            'serviceReminders' => ['count' => $due->count(), 'items' => $due->all()],
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
