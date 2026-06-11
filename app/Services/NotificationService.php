<?php

namespace App\Services;

use App\Models\Maintenance;
use App\Models\NotificationReadReceipt;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Number of days a DRAFT maintenance may sit untouched before it is flagged as
     * needing follow-up.
     */
    private const STALE_DRAFT_DAYS = 7;

    /**
     * Maximum number of items rendered in the navbar dropdown.
     */
    public const PAGE_SIZE = 7;

    public function __construct(
        private readonly ServiceReminderService $serviceReminders,
        private readonly VehiclePhotoMonitorService $photoMonitor,
        private readonly VehicleInsuranceMonitorService $insuranceMonitor,
    ) {}

    /**
     * Build the live navbar notifications for a user: maintenance awaiting confirmation,
     * maintenance needing follow-up, and vehicles due for service. Each category is gated
     * by the permission required to act on it.
     *
     * @return array{count: int, items: array<int, array<string, mixed>>}
     */
    public function forUser(?User $user): array
    {
        return $this->forUserPage($user, 0);
    }

    /**
     * Build a page of live navbar notifications for the given user.
     *
     * @return array{count: int, items: array<int, array<string, mixed>>, offset: int, limit: int, next_offset: int, has_more: bool}
     */
    public function forUserPage(?User $user, int $offset = 0): array
    {
        if ($user === null) {
            return [
                'count' => 0,
                'items' => [],
                'offset' => 0,
                'limit' => self::PAGE_SIZE,
                'next_offset' => self::PAGE_SIZE,
                'has_more' => false,
            ];
        }

        $offset = max(0, $offset);
        $items = $this->unreadItemsForUser($user);

        return [
            'count' => count($items),
            'items' => array_slice($items, $offset, self::PAGE_SIZE),
            'offset' => $offset,
            'limit' => self::PAGE_SIZE,
            'next_offset' => $offset + self::PAGE_SIZE,
            'has_more' => $offset + self::PAGE_SIZE < count($items),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function unreadItemsForUser(User $user): array
    {
        $items = $this->itemsForUser($user);
        $readKeys = NotificationReadReceipt::where('user_id', $user->id)
            ->whereIn('notification_key', collect($items)->pluck('key'))
            ->pluck('notification_key')
            ->all();

        return collect($items)
            ->reject(fn (array $item): bool => in_array($item['key'], $readKeys, true))
            ->values()
            ->all();
    }

    /**
     * All current reminder items for a user, without the in-app read-receipt
     * filtering. Used by the Telegram dispatcher, which dedupes via its own send
     * ledger rather than the navbar read state. Shares the exact source of truth
     * (and permission gating) as the navbar dropdown.
     *
     * @return array<int, array<string, mixed>>
     */
    public function pushableItemsForUser(User $user): array
    {
        return $this->itemsForUser($user);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function itemsForUser(User $user): array
    {
        $canView = $user->hasPermission('operational.maintenances.view') === true;
        $canApprove = $user->hasPermission('operational.maintenances.approve') === true;
        $canRemind = $user->hasPermission('operational.maintenances.reminders') === true;
        $canPhotoRemind = $user->hasPermission('operational.vehicle_photos.reminders') === true;
        $canInsuranceRemind = $user->hasPermission('master.vehicle_insurances.reminders') === true;

        $plates = Vehicle::pluck('plat_new');
        $items = [];

        if ($canApprove) {
            foreach ($this->awaitingConfirmation($plates) as $maintenance) {
                $items[] = [
                    'key' => $this->maintenanceKey('awaiting-confirmation', $maintenance),
                    'type' => 'maintenance.awaiting-confirmation',
                    'icon' => 'bx-time-five',
                    'color' => 'info',
                    'title' => $maintenance->no_pol,
                    'subtitle' => 'Awaiting confirmation',
                    'link' => route('maintenances.show', $maintenance),
                ];
            }
        }

        if ($canView) {
            foreach ($this->staleDrafts($plates) as $maintenance) {
                $items[] = [
                    'key' => $this->maintenanceKey('stale-draft', $maintenance),
                    'type' => 'maintenance.stale-draft',
                    'icon' => 'bx-error-circle',
                    'color' => 'warning',
                    'title' => $maintenance->no_pol,
                    'subtitle' => 'Maintenance needs follow-up',
                    'link' => route('maintenances.show', $maintenance),
                ];
            }
        }

        // Service reminders are gated by a dedicated permission so the audience can be
        // defined independently of maintenance access. dueVehicles() is company-scoped
        // via the Vehicle CompanyTenantScope (bypassed by management.tenant.bypass).
        if ($canRemind) {
            foreach ($this->serviceReminders->dueVehicles() as $due) {
                $items[] = [
                    'key' => $this->serviceDueKey($due),
                    'type' => 'vehicle.service-due',
                    'icon' => 'bx-wrench',
                    'color' => 'danger',
                    'title' => $due['plate'],
                    'subtitle' => 'Due for service · '.$due['over'].' km over',
                    'link' => $due['link'],
                ];
            }
        }

        // Monthly photo reminders are gated by a dedicated permission so the audience can be
        // defined independently of maintenance access. incompleteVehicles() is company-scoped
        // via the Vehicle CompanyTenantScope (bypassed by management.tenant.bypass).
        if ($canPhotoRemind) {
            foreach ($this->photoMonitor->incompleteVehicles() as $missing) {
                $items[] = [
                    'key' => $this->photoMissingKey($missing),
                    'type' => 'vehicle.photo-missing',
                    'icon' => 'bx-camera',
                    'color' => 'warning',
                    'title' => $missing['plate'],
                    'subtitle' => 'Foto '.$missing['period_label'].' belum lengkap ('.$missing['completed'].'/'.$missing['total'].')',
                    'link' => $missing['link'],
                ];
            }
        }

        // Insurance expiry reminders (H-30/14/7/3), gated by a dedicated permission and
        // company-scoped via the VehicleInsurance CompanyTenantScope (bypassed by
        // management.tenant.bypass).
        if ($canInsuranceRemind) {
            foreach ($this->insuranceMonitor->overduePolicies() as $policy) {
                $items[] = [
                    'key' => 'vehicle:insurance-overdue:'.$policy['insurance_id'],
                    'type' => 'vehicle.insurance-overdue',
                    'icon' => 'bx-shield-x',
                    'color' => 'danger',
                    'title' => $policy['plate'],
                    'subtitle' => 'Asuransi kedaluwarsa '.$policy['end_date'].' · '.$policy['days_overdue'].' hari lalu',
                    'link' => $policy['link'],
                ];
            }

            foreach ($this->insuranceMonitor->expiringPolicies() as $policy) {
                $items[] = [
                    'key' => $this->insuranceExpiringKey($policy),
                    'type' => 'vehicle.insurance-expiring',
                    'icon' => 'bx-shield-quarter',
                    'color' => $policy['threshold'] <= 7 ? 'danger' : 'warning',
                    'title' => $policy['plate'],
                    'subtitle' => 'Asuransi berakhir '.$policy['end_date'].' · H-'.$policy['days_left'],
                    'link' => $policy['link'],
                ];
            }
        }

        return $items;
    }

    public function markRead(User $user, string $key): void
    {
        $item = collect($this->itemsForUser($user))->firstWhere('key', $key);

        if ($item === null) {
            return;
        }

        NotificationReadReceipt::updateOrCreate(
            [
                'user_id' => $user->id,
                'notification_key' => $key,
            ],
            [
                'notification_type' => $item['type'],
                'read_at' => now(),
            ]
        );
    }

    private function maintenanceKey(string $type, Maintenance $maintenance): string
    {
        return "maintenance:{$type}:{$maintenance->id}:".($maintenance->updated_at?->timestamp ?? 0);
    }

    /**
     * @param  array<string, mixed>  $policy
     */
    private function insuranceExpiringKey(array $policy): string
    {
        // The threshold bucket in the key rolls the reminder forward as expiry nears,
        // so a tighter bucket (e.g. H-7) re-fires even after H-30 was dismissed.
        return 'vehicle:insurance-expiring:'.$policy['insurance_id'].':'.$policy['threshold_value'];
    }

    /**
     * @param  array<string, mixed>  $missing
     */
    private function photoMissingKey(array $missing): string
    {
        // The Y-m period makes the key roll over each month so the reminder re-fires monthly.
        return 'vehicle:photo-missing:'.$missing['vehicle_id'].':'.$missing['period_value'];
    }

    /**
     * @param  array<string, mixed>  $due
     */
    private function serviceDueKey(array $due): string
    {
        return 'vehicle:service-due:'.$due['vehicle_id'].':'.$due['threshold_value'];
    }

    /**
     * @param  Collection<int, string>  $plates
     * @return Collection<int, Maintenance>
     */
    private function awaitingConfirmation(Collection $plates): Collection
    {
        return Maintenance::where('status', Maintenance::STATUS_COMPLETED)
            ->where('is_confirmed', false)
            ->whereIn('no_pol', $plates)
            ->latest('updated_at')
            ->get();
    }

    /**
     * @param  Collection<int, string>  $plates
     * @return Collection<int, Maintenance>
     */
    private function staleDrafts(Collection $plates): Collection
    {
        return Maintenance::where('status', Maintenance::STATUS_DRAFT)
            ->where('updated_at', '<', now()->subDays(self::STALE_DRAFT_DAYS))
            ->whereIn('no_pol', $plates)
            ->latest('updated_at')
            ->get();
    }
}
