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

    public function __construct(private readonly ServiceReminderService $serviceReminders) {}

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
     * @return array<int, array<string, mixed>>
     */
    private function itemsForUser(User $user): array
    {
        $canView = $user->hasPermission('operational.maintenances.view') === true;
        $canApprove = $user->hasPermission('operational.maintenances.approve') === true;

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
