<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Driver;
use App\Models\Record;
use App\Models\Vehicle;
use App\Models\Company;
use App\Models\Destination;
use App\Models\Maintenances;
use App\Models\TypeMaintenances;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->get('query', ''));
        if (mb_strlen($query) < 2) {
            return response()->json(['items' => []]);
        }

        $user = $request->user();
        $items = [];

        foreach ($this->filterMenuItems($query, $user) as $item) {
            $items[] = $item;
        }

        foreach ($this->searchDataItems($query, $user) as $item) {
            $items[] = $item;
        }

        return response()->json(['items' => $items]);
    }

    private function filterMenuItems(string $query, $user): array
    {
        $menu = config('menu');
        $items = [];
        $terms = preg_split('/\s+/', mb_strtolower($query));

        $canView = function ($permission) use ($user) {
            return !$permission || $user?->hasPermission($permission);
        };

        $matches = function (array $item) use ($terms) {
            $keywords = $item['keywords'] ?? [];
            $haystack = mb_strtolower(
                ($item['title'] ?? '') . ' ' .
                ($item['description'] ?? '') . ' ' .
                implode(' ', $keywords)
            );

            foreach ($terms as $term) {
                if ($term === '') {
                    continue;
                }
                if (mb_strpos($haystack, $term) === false) {
                    return false;
                }
            }

            return true;
        };

        $pushItem = function (array $item) use (&$items, $matches) {
            if (!$matches($item)) {
                return;
            }

            $items[] = [
                'title' => $item['title'],
                'description' => $item['description'] ?? $item['title'],
                'url' => route($item['route']),
                'icon' => $item['icon'],
                'group' => 'Menu',
            ];
        };

        foreach (($menu['main'] ?? []) as $item) {
            if ($canView($item['permission'] ?? null)) {
                $pushItem($item);
            }
        }

        foreach (($menu['sections'] ?? []) as $section) {
            foreach (($section['items'] ?? []) as $item) {
                if ($canView($item['permission'] ?? null)) {
                    $pushItem($item);
                }
            }
        }

        return $items;
    }

    private function searchDataItems(string $query, $user): array
    {
        $items = [];

        if ($user?->hasPermission('management.users.view')) {
            $users = User::query()
                ->where('name', 'like', '%' . $query . '%')
                ->orWhere('username', 'like', '%' . $query . '%')
                ->orWhere('email', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            foreach ($users as $userItem) {
                $items[] = [
                    'title' => $userItem->name,
                    'description' => $userItem->username ?? $userItem->email,
                    'url' => route('users.show', $userItem->id),
                    'icon' => 'bx-user',
                    'group' => 'Data',
                ];
            }
        }

        if ($user?->hasPermission('management.roles.view')) {
            $roles = Role::query()
                ->where('display_name', 'like', '%' . $query . '%')
                ->orWhere('name', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            foreach ($roles as $role) {
                $items[] = [
                    'title' => $role->display_name ?? $role->name,
                    'description' => $role->name,
                    'url' => route('roles.show', $role->id),
                    'icon' => 'bx-shield',
                    'group' => 'Data',
                ];
            }
        }

        if ($user?->hasPermission('maintenances.view')) {
            $maintenances = Maintenances::query()
                ->where('code_mt', 'like', '%' . $query . '%')
                ->orWhere('no_pol', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            foreach ($maintenances as $maintenance) {
                $items[] = [
                    'title' => $maintenance->code_mt,
                    'description' => 'No Pol: ' . $maintenance->no_pol,
                    'url' => route('maintenances.show', $maintenance->code_mt),
                    'icon' => 'bx-wrench',
                    'group' => 'Data',
                ];
            }
        }

        if ($user?->hasPermission('record.view')) {
            $records = Record::query()
                ->where('no_pol', 'like', '%' . $query . '%')
                ->orWhere('driver_1', 'like', '%' . $query . '%')
                ->orWhere('driver_2', 'like', '%' . $query . '%')
                ->orWhere('destination', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            foreach ($records as $record) {
                $items[] = [
                    'title' => 'Record #' . $record->id,
                    'description' => $record->no_pol . ' • ' . $record->destination,
                    'url' => route('record.show', $record->id),
                    'icon' => 'bx-gas-pump',
                    'group' => 'Data',
                ];
            }
        }

        if ($user?->hasPermission('master.drivers.view')) {
            $drivers = Driver::query()
                ->where('name', 'like', '%' . $query . '%')
                ->orWhere('no_identity', 'like', '%' . $query . '%')
                ->orWhere('nik', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            foreach ($drivers as $driver) {
                $items[] = [
                    'title' => $driver->name,
                    'description' => $driver->no_identity ?? $driver->nik,
                    'url' => route('driver.index'),
                    'icon' => 'bx-id-card',
                    'group' => 'Data',
                ];
            }
        }

        if ($user?->hasPermission('master.vehicles.view')) {
            $vehicles = Vehicle::query()
                ->where('plat_new', 'like', '%' . $query . '%')
                ->orWhere('plat_old', 'like', '%' . $query . '%')
                ->orWhere('type', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            foreach ($vehicles as $vehicle) {
                $items[] = [
                    'title' => $vehicle->plat_new,
                    'description' => $vehicle->type ?? 'Kendaraan',
                    'url' => route('vehicle.index'),
                    'icon' => 'bx-car',
                    'group' => 'Data',
                ];
            }
        }

        if ($user?->hasPermission('master.destinations.view')) {
            $destinations = Destination::query()
                ->where('destination', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            foreach ($destinations as $destination) {
                $items[] = [
                    'title' => $destination->destination,
                    'description' => $destination->desc ?? 'Tujuan',
                    'url' => route('destination.index'),
                    'icon' => 'bx-map',
                    'group' => 'Data',
                ];
            }
        }

        if ($user?->hasPermission('master.companies.view')) {
            $companies = Company::query()
                ->where('name', 'like', '%' . $query . '%')
                ->orWhere('code', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            foreach ($companies as $company) {
                $items[] = [
                    'title' => $company->name,
                    'description' => $company->code,
                    'url' => route('company.index'),
                    'icon' => 'bx-category',
                    'group' => 'Data',
                ];
            }
        }

        if ($user?->hasPermission('master.type_maintenances.view')) {
            $types = TypeMaintenances::query()
                ->where('name', 'like', '%' . $query . '%')
                ->limit(5)
                ->get();

            foreach ($types as $type) {
                $items[] = [
                    'title' => $type->name,
                    'description' => 'Tipe Maintenance',
                    'url' => route('type-maintenance.index'),
                    'icon' => 'bx-wrench',
                    'group' => 'Data',
                ];
            }
        }

        return $items;
    }
}
