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
    | show route used to build the result URL. `title` is the model attribute
    | rendered as the result heading; `description` is a list of attributes
    | joined with " · " (empty parts are dropped) for the result subtitle.
    |
    | These are plain field names (not closures) so the config stays cacheable
    | by `config:cache` / `optimize`.
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
            'label' => 'Supir',
            'icon' => 'bx-id-card',
            'permission' => 'master.drivers.view',
            'route' => 'drivers.show',
            'columns' => ['name', 'nik', 'no_identity', 'no_phone_1'],
            'title' => 'name',
            'description' => ['nik', 'no_phone_1'],
        ],

        'vehicle' => [
            'model' => Vehicle::class,
            'label' => 'Kendaraan',
            'icon' => 'bx-car',
            'permission' => 'master.vehicles.view',
            'route' => 'vehicles.show',
            'columns' => ['plat_new', 'machine_number', 'vin_number'],
            'title' => 'plat_new',
            'description' => ['type', 'machine_number'],
        ],

        'company' => [
            'model' => Company::class,
            'label' => 'Perusahaan',
            'icon' => 'bx-buildings',
            'permission' => 'master.companies.view',
            'route' => 'companies.show',
            'columns' => ['code', 'name', 'email', 'phone'],
            'title' => 'name',
            'description' => ['code', 'email'],
        ],

        'destination' => [
            'model' => Destination::class,
            'label' => 'Tujuan',
            'icon' => 'bx-map',
            'permission' => 'master.destinations.view',
            'route' => 'destinations.show',
            'columns' => ['code', 'name'],
            'title' => 'name',
            'description' => ['code'],
        ],

        'type-maintenance' => [
            'model' => TypeMaintenances::class,
            'label' => 'Tipe Pemeliharaan',
            'icon' => 'bx-wrench',
            'permission' => 'master.type_maintenances.view',
            'route' => 'type-maintenances.show',
            'columns' => ['name', 'jenis'],
            'title' => 'name',
            'description' => ['jenis'],
        ],

        'vehicle-type' => [
            'model' => VehicleType::class,
            'label' => 'Tipe Kendaraan',
            'icon' => 'bx-category',
            'permission' => 'master.vehicle_types.view',
            'route' => 'vehicle-types.show',
            'columns' => ['code', 'name', 'descriptions'],
            'title' => 'name',
            'description' => ['code', 'descriptions'],
        ],

        'vehicle-brand' => [
            'model' => VehicleBrand::class,
            'label' => 'Merek Kendaraan',
            'icon' => 'bx-purchase-tag',
            'permission' => 'master.vehicle_brands.view',
            'route' => 'vehicle-brands.show',
            'columns' => ['code', 'name', 'descriptions'],
            'title' => 'name',
            'description' => ['code', 'descriptions'],
        ],

        'fuel-type' => [
            'model' => FuelType::class,
            'label' => 'Jenis BBM',
            'icon' => 'bx-gas-pump',
            'permission' => 'master.fuel_types.view',
            'route' => 'fuel-types.show',
            'columns' => ['name', 'type', 'merk'],
            'title' => 'name',
            'description' => ['type', 'merk'],
        ],

        'record' => [
            'model' => Record::class,
            'label' => 'Catatan Perjalanan',
            'icon' => 'bx-navigation',
            'permission' => 'operational.records.view',
            'route' => 'records.show',
            'columns' => ['no_pol', 'destination'],
            'title' => 'no_pol',
            'description' => ['destination', 'date'],
        ],

        'maintenance' => [
            'model' => Maintenance::class,
            'label' => 'Pemeliharaan',
            'icon' => 'bx-wrench',
            'permission' => 'operational.maintenances.view',
            'route' => 'maintenances.show',
            'columns' => ['code_mt', 'no_pol', 'description'],
            'title' => 'code_mt',
            'description' => ['no_pol', 'description'],
        ],

    ],

];
