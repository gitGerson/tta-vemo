<?php

use App\Models\Company;
use App\Models\Destination;
use App\Models\Driver;
use App\Models\FuelType;
use App\Models\Maintenance;
use App\Models\Record;
use App\Models\TypeMaintenances;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleType;

return [

    /*
    |--------------------------------------------------------------------------
    | Global Record Search
    |--------------------------------------------------------------------------
    |
    | Drives the navbar global record search (SearchService). Each entry maps a
    | model to the columns searched (LIKE), the permission gating it, and the
    | show route used to build the result URL. `title`/`description` closures
    | receive a model instance and return the strings rendered in the dropdown.
    |
    | Tenant scoping and soft-delete exclusion are handled by the models' global
    | scopes — the service queries with a plain query() and adds no special case.
    |
    */

    'min_query_length' => 2,
    'per_model_limit' => 5,
    'total_limit' => 30,

    'models' => [

        'driver' => [
            'model' => Driver::class,
            'label' => 'Driver',
            'icon' => 'bx-id-card',
            'permission' => 'master.drivers.view',
            'route' => 'drivers.show',
            'columns' => ['name', 'nik', 'no_identity', 'no_phone_1'],
            'title' => fn ($m) => $m->name,
            'description' => fn ($m) => trim(($m->nik ?? '').' · '.($m->no_phone_1 ?? ''), ' ·'),
        ],

        'vehicle' => [
            'model' => Vehicle::class,
            'label' => 'Vehicle',
            'icon' => 'bx-car',
            'permission' => 'master.vehicles.view',
            'route' => 'vehicles.show',
            'columns' => ['plat_new', 'machine_number', 'vin_number'],
            'title' => fn ($m) => $m->plat_new,
            'description' => fn ($m) => trim(($m->type ?? '').' · '.($m->machine_number ?? ''), ' ·'),
        ],

        'company' => [
            'model' => Company::class,
            'label' => 'Company',
            'icon' => 'bx-buildings',
            'permission' => 'master.companies.view',
            'route' => 'companies.show',
            'columns' => ['code', 'name', 'email', 'phone'],
            'title' => fn ($m) => $m->name,
            'description' => fn ($m) => trim(($m->code ?? '').' · '.($m->email ?? ''), ' ·'),
        ],

        'destination' => [
            'model' => Destination::class,
            'label' => 'Destination',
            'icon' => 'bx-map',
            'permission' => 'master.destinations.view',
            'route' => 'destinations.show',
            'columns' => ['code', 'name'],
            'title' => fn ($m) => $m->name,
            'description' => fn ($m) => (string) ($m->code ?? ''),
        ],

        'type-maintenance' => [
            'model' => TypeMaintenances::class,
            'label' => 'Type Maintenance',
            'icon' => 'bx-wrench',
            'permission' => 'master.type_maintenances.view',
            'route' => 'type-maintenances.show',
            'columns' => ['name', 'jenis'],
            'title' => fn ($m) => $m->name,
            'description' => fn ($m) => (string) ($m->jenis ?? ''),
        ],

        'vehicle-type' => [
            'model' => VehicleType::class,
            'label' => 'Vehicle Type',
            'icon' => 'bx-category',
            'permission' => 'master.vehicle_types.view',
            'route' => 'vehicle-types.show',
            'columns' => ['code', 'name', 'descriptions'],
            'title' => fn ($m) => $m->name,
            'description' => fn ($m) => trim(($m->code ?? '').' · '.($m->descriptions ?? ''), ' ·'),
        ],

        'vehicle-brand' => [
            'model' => VehicleBrand::class,
            'label' => 'Vehicle Brand',
            'icon' => 'bx-purchase-tag',
            'permission' => 'master.vehicle_brands.view',
            'route' => 'vehicle-brands.show',
            'columns' => ['code', 'name', 'descriptions'],
            'title' => fn ($m) => $m->name,
            'description' => fn ($m) => trim(($m->code ?? '').' · '.($m->descriptions ?? ''), ' ·'),
        ],

        'fuel-type' => [
            'model' => FuelType::class,
            'label' => 'Fuel Type',
            'icon' => 'bx-gas-pump',
            'permission' => 'master.fuel_types.view',
            'route' => 'fuel-types.show',
            'columns' => ['name', 'type', 'merk'],
            'title' => fn ($m) => $m->name,
            'description' => fn ($m) => trim(($m->type ?? '').' · '.($m->merk ?? ''), ' ·'),
        ],

        'record' => [
            'model' => Record::class,
            'label' => 'Trip Record',
            'icon' => 'bx-navigation',
            'permission' => 'operational.records.view',
            'route' => 'records.show',
            'columns' => ['no_pol', 'destination'],
            'title' => fn ($m) => $m->no_pol,
            'description' => fn ($m) => trim(($m->destination ?? '').' · '.($m->date ?? ''), ' ·'),
        ],

        'maintenance' => [
            'model' => Maintenance::class,
            'label' => 'Maintenance',
            'icon' => 'bx-wrench',
            'permission' => 'operational.maintenances.view',
            'route' => 'maintenances.show',
            'columns' => ['code_mt', 'no_pol', 'description'],
            'title' => fn ($m) => $m->code_mt,
            'description' => fn ($m) => trim(($m->no_pol ?? '').' · '.($m->description ?? ''), ' ·'),
        ],

    ],

];
