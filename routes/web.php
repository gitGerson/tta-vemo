<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\FormDemoController;
use App\Http\Controllers\FuelTypeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QrScanController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportGeneratorController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TelegramLinkController;
use App\Http\Controllers\TypeMaintenanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleBrandController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleInsuranceController;
use App\Http\Controllers\VehicleLabelController;
use App\Http\Controllers\VehiclePhotoController;
use App\Http\Controllers\VehicleTypeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return to login
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Telegram bot updates are received via long-polling (telegram:poll-updates),
// not an inbound webhook — see app/Console/Commands/PollTelegramUpdates.php.

// Route group middleware for authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read', [NotificationController::class, 'read'])->name('notifications.read');

    // Global record search (per-model view permissions filtered inside the service).
    Route::get('/search', SearchController::class)->name('search');

    // Self-service Telegram linking (any authenticated user).
    Route::get('/account/telegram', [TelegramLinkController::class, 'show'])->name('account.telegram');
    Route::delete('/account/telegram', [TelegramLinkController::class, 'unlink'])->name('account.telegram.unlink');

    // User management routes with permissions
    Route::middleware(['permission:management.users.create'])->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users/verify-ldap', [UserController::class, 'verifyLdap'])->name('users.verify-ldap');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware(['permission:management.users.delete'])->group(function () {
        Route::get('/users/trash', [UserController::class, 'trash'])->name('users.trash');
        Route::get('/users/trash/data', [UserController::class, 'getTrashData'])->name('users.trash.data');
    });

    Route::middleware(['permission:management.users.view'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [UserController::class, 'getData'])->name('users.data');
        Route::get('/users/logs', [UserController::class, 'logs'])->name('users.logs');
        Route::get('/users/logs/data', [UserController::class, 'getLogsData'])->name('users.logs.data');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/logs', [UserController::class, 'userLogs'])->name('users.user-logs');
        Route::get('/users/{user}/logs/data', [UserController::class, 'getUserLogsData'])->name('users.user-logs.data');
    });

    Route::middleware(['permission:management.users.edit'])->group(function () {
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}', [UserController::class, 'update']);
    });

    Route::middleware(['permission:management.users.permissions'])->group(function () {
        Route::put('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.update-permissions');
        Route::post('/users/{user}/apply-template', [UserController::class, 'applyTemplate'])->name('users.apply-template');
        Route::get('/roles/{role}/permissions', [UserController::class, 'getTemplatePermissions'])->name('roles.permissions');
    });

    Route::middleware(['permission:management.users.delete'])->group(function () {
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::delete('/users/{user}/force-delete', [UserController::class, 'forceDelete'])->withTrashed()->name('users.force-delete');
    });

    Route::middleware(['permission:management.users.restore'])->group(function () {
        Route::post('/users/{user}/restore', [UserController::class, 'restore'])->withTrashed()->name('users.restore');
    });

    // Role management routes with permissions
    Route::middleware(['permission:management.roles.create'])->group(function () {
        Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware(['permission:management.roles.view'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/data', [RoleController::class, 'getData'])->name('roles.data');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });

    Route::middleware(['permission:management.roles.edit'])->group(function () {
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::patch('/roles/{role}', [RoleController::class, 'update']);
    });

    Route::middleware(['permission:management.roles.delete'])->group(function () {
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    // Driver master data routes with permissions
    Route::middleware(['permission:master.drivers.view'])->group(function () {
        Route::get('/drivers', [DriverController::class, 'index'])->name('drivers.index');
        Route::get('/drivers/data', [DriverController::class, 'getData'])->name('drivers.data');
        Route::get('/drivers/emp-info', [DriverController::class, 'getEmpInfo'])->name('drivers.empInfo');
    });

    Route::middleware(['permission:master.drivers.create'])->group(function () {
        Route::post('/drivers', [DriverController::class, 'store'])->name('drivers.store');
    });

    Route::middleware(['permission:master.drivers.edit'])->group(function () {
        Route::put('/drivers/{driver}', [DriverController::class, 'update'])->name('drivers.update');
    });

    Route::middleware(['permission:master.drivers.delete'])->group(function () {
        Route::get('/drivers/trash', [DriverController::class, 'trash'])->name('drivers.trash');
        Route::get('/drivers/trash/data', [DriverController::class, 'getTrashData'])->name('drivers.trash.data');
        Route::delete('/drivers/{driver}', [DriverController::class, 'destroy'])->name('drivers.destroy');
    });

    Route::middleware(['permission:master.drivers.restore'])->group(function () {
        Route::post('/drivers/{driver}/restore', [DriverController::class, 'restore'])->withTrashed()->name('drivers.restore');
    });

    Route::middleware(['permission:master.drivers.force_delete'])->group(function () {
        Route::delete('/drivers/{driver}/force-delete', [DriverController::class, 'forceDelete'])->withTrashed()->name('drivers.force-delete');
    });

    Route::middleware(['permission:master.drivers.view'])->group(function () {
        Route::get('/drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');
    });

    // Company master data routes with permissions
    Route::middleware(['permission:master.companies.view'])->group(function () {
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/companies/data', [CompanyController::class, 'getData'])->name('companies.data');
        Route::get('/companies/{company}/drivers/data', [CompanyController::class, 'getDriverData'])->name('companies.drivers.data');
        Route::get('/companies/{company}/users/data', [CompanyController::class, 'getUserData'])->name('companies.users.data');
        Route::get('/companies/{company}/vehicles/data', [CompanyController::class, 'getVehicleData'])->name('companies.vehicles.data');
    });

    Route::middleware(['permission:master.companies.create'])->group(function () {
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    });

    Route::middleware(['permission:master.companies.edit'])->group(function () {
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    });

    Route::middleware(['permission:master.companies.delete'])->group(function () {
        Route::get('/companies/trash', [CompanyController::class, 'trash'])->name('companies.trash');
        Route::get('/companies/trash/data', [CompanyController::class, 'getTrashData'])->name('companies.trash.data');
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    });

    Route::middleware(['permission:master.companies.restore'])->group(function () {
        Route::post('/companies/{company}/restore', [CompanyController::class, 'restore'])->withTrashed()->name('companies.restore');
    });

    Route::middleware(['permission:master.companies.force_delete'])->group(function () {
        Route::delete('/companies/{company}/force-delete', [CompanyController::class, 'forceDelete'])->withTrashed()->name('companies.force-delete');
    });

    Route::middleware(['permission:master.companies.view'])->group(function () {
        Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    });

    // Vehicle master data routes with permissions
    Route::middleware(['permission:master.vehicles.view'])->group(function () {
        Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
        Route::get('/vehicles/data', [VehicleController::class, 'getData'])->name('vehicles.data');
        Route::get('/vehicles/{vehicle}/snapshots/data', [VehicleController::class, 'getSnapshotData'])->name('vehicles.snapshots.data');
        Route::get('/vehicles/{vehicle}/insurances/data', [VehicleInsuranceController::class, 'getDataForVehicle'])->name('vehicles.insurances.data');
    });

    Route::middleware(['permission:master.vehicles.create'])->group(function () {
        Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
        Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    });

    Route::middleware(['permission:master.vehicles.edit'])->group(function () {
        Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
        Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    });

    Route::middleware(['permission:master.vehicles.delete'])->group(function () {
        Route::get('/vehicles/trash', [VehicleController::class, 'trash'])->name('vehicles.trash');
        Route::get('/vehicles/trash/data', [VehicleController::class, 'getTrashData'])->name('vehicles.trash.data');
        Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicles.destroy');
    });

    Route::middleware(['permission:master.vehicles.restore'])->group(function () {
        Route::post('/vehicles/{vehicle}/restore', [VehicleController::class, 'restore'])->withTrashed()->name('vehicles.restore');
    });

    Route::middleware(['permission:master.vehicles.force_delete'])->group(function () {
        Route::delete('/vehicles/{vehicle}/force-delete', [VehicleController::class, 'forceDelete'])->withTrashed()->name('vehicles.force-delete');
    });

    Route::middleware(['permission:master.vehicles.view'])->group(function () {
        Route::get('/vehicles/{vehicle}/label', VehicleLabelController::class)->name('vehicles.label');
        Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
    });

    // QR scan: look up a vehicle by plate and reach quick actions
    Route::middleware(['permission:operational.qr_scan.view'])->group(function () {
        Route::get('/qr-scan', [QrScanController::class, 'index'])->name('qr-scan.index');
        Route::post('/qr-scan/resolve', [QrScanController::class, 'resolve'])->name('qr-scan.resolve');
    });

    // Vehicle insurance master data routes with permissions
    Route::middleware(['permission:master.vehicle_insurances.view'])->group(function () {
        Route::get('/vehicle-insurances', [VehicleInsuranceController::class, 'index'])->name('vehicle-insurances.index');
        Route::get('/vehicle-insurances/data', [VehicleInsuranceController::class, 'getData'])->name('vehicle-insurances.data');
        Route::get('/vehicle-insurances/{vehicleInsurance}/snapshots/data', [VehicleInsuranceController::class, 'getSnapshotData'])->name('vehicle-insurances.snapshots.data');
    });

    Route::middleware(['permission:master.vehicle_insurances.create'])->group(function () {
        Route::get('/vehicle-insurances/create', [VehicleInsuranceController::class, 'create'])->name('vehicle-insurances.create');
        Route::post('/vehicle-insurances', [VehicleInsuranceController::class, 'store'])->name('vehicle-insurances.store');
    });

    Route::middleware(['permission:master.vehicle_insurances.edit'])->group(function () {
        Route::get('/vehicle-insurances/{vehicleInsurance}/edit', [VehicleInsuranceController::class, 'edit'])->name('vehicle-insurances.edit');
        Route::put('/vehicle-insurances/{vehicleInsurance}', [VehicleInsuranceController::class, 'update'])->name('vehicle-insurances.update');
    });

    Route::middleware(['permission:master.vehicle_insurances.delete'])->group(function () {
        Route::get('/vehicle-insurances/trash', [VehicleInsuranceController::class, 'trash'])->name('vehicle-insurances.trash');
        Route::get('/vehicle-insurances/trash/data', [VehicleInsuranceController::class, 'getTrashData'])->name('vehicle-insurances.trash.data');
        Route::delete('/vehicle-insurances/{vehicleInsurance}', [VehicleInsuranceController::class, 'destroy'])->name('vehicle-insurances.destroy');
    });

    Route::middleware(['permission:master.vehicle_insurances.restore'])->group(function () {
        Route::post('/vehicle-insurances/{vehicleInsurance}/restore', [VehicleInsuranceController::class, 'restore'])->withTrashed()->name('vehicle-insurances.restore');
    });

    Route::middleware(['permission:master.vehicle_insurances.force_delete'])->group(function () {
        Route::delete('/vehicle-insurances/{vehicleInsurance}/force-delete', [VehicleInsuranceController::class, 'forceDelete'])->withTrashed()->name('vehicle-insurances.force-delete');
    });

    Route::middleware(['permission:master.vehicle_insurances.view'])->group(function () {
        Route::get('/vehicle-insurances/{vehicleInsurance}', [VehicleInsuranceController::class, 'show'])->name('vehicle-insurances.show');
    });

    // Vehicle monthly photo (Foto Kendaraan) routes with permissions.
    // The create route is declared before the show route so the static
    // /vehicle-photos/create path is matched first and not captured by the dynamic
    // /vehicle-photos/{vehiclePhoto}: the route key is now an alphanumeric hashid, so the
    // two can no longer be told apart by a numeric constraint.
    Route::middleware(['permission:operational.vehicle_photos.create'])->group(function () {
        Route::get('/vehicle-photos/create', [VehiclePhotoController::class, 'create'])->name('vehicle-photos.create');
        Route::post('/vehicle-photos', [VehiclePhotoController::class, 'store'])->name('vehicle-photos.store');
    });

    Route::middleware(['permission:operational.vehicle_photos.view'])->group(function () {
        Route::get('/vehicles/{vehicle}/photos/data', [VehiclePhotoController::class, 'getDataForVehicle'])->name('vehicles.photos.data');
        Route::get('/vehicle-photos/data', [VehiclePhotoController::class, 'getData'])->name('vehicle-photos.data');
        Route::get('/vehicle-photos/{vehiclePhoto}', [VehiclePhotoController::class, 'show'])->name('vehicle-photos.show');
        Route::post('/vehicle-photos/{vehiclePhoto}/comments', [VehiclePhotoController::class, 'storeComment'])->name('vehicle-photos.comments.store');
        Route::delete('/vehicle-photos/{vehiclePhoto}/comments/{comment}', [VehiclePhotoController::class, 'destroyComment'])->name('vehicle-photos.comments.destroy');
    });

    Route::middleware(['permission:operational.vehicle_photos.edit'])->group(function () {
        Route::get('/vehicle-photos/{vehiclePhoto}/edit', [VehiclePhotoController::class, 'edit'])->name('vehicle-photos.edit');
        Route::put('/vehicle-photos/{vehiclePhoto}', [VehiclePhotoController::class, 'update'])->name('vehicle-photos.update');
    });

    Route::middleware(['permission:operational.vehicle_photos.delete'])->group(function () {
        Route::delete('/vehicle-photos/{vehiclePhoto}', [VehiclePhotoController::class, 'destroy'])->name('vehicle-photos.destroy');
    });

    // Record (Data Perjalanan) operational routes with permissions
    Route::middleware(['permission:operational.records.view'])->group(function () {
        Route::get('/records', [RecordController::class, 'index'])->name('records.index');
        Route::get('/records/data', [RecordController::class, 'getData'])->name('records.data');
        Route::get('/records/export', [RecordController::class, 'exportExcel'])->name('records.export');
        Route::get('/records/{record}/snapshots/data', [RecordController::class, 'getSnapshotData'])->name('records.snapshots.data');
    });

    Route::middleware(['permission:operational.records.create'])->group(function () {
        Route::get('/records/create', [RecordController::class, 'create'])->name('records.create');
        Route::post('/records', [RecordController::class, 'store'])->name('records.store');
    });

    Route::middleware(['permission:operational.records.edit'])->group(function () {
        Route::get('/records/{record}/edit', [RecordController::class, 'edit'])->name('records.edit');
        Route::put('/records/{record}', [RecordController::class, 'update'])->name('records.update');
    });

    Route::middleware(['permission:operational.records.delete'])->group(function () {
        Route::get('/records/trash', [RecordController::class, 'trash'])->name('records.trash');
        Route::get('/records/trash/data', [RecordController::class, 'getTrashData'])->name('records.trash.data');
        Route::delete('/records/{record}', [RecordController::class, 'destroy'])->name('records.destroy');
    });

    Route::middleware(['permission:operational.records.restore'])->group(function () {
        Route::post('/records/{record}/restore', [RecordController::class, 'restore'])->withTrashed()->name('records.restore');
    });

    Route::middleware(['permission:operational.records.force_delete'])->group(function () {
        Route::delete('/records/{record}/force-delete', [RecordController::class, 'forceDelete'])->withTrashed()->name('records.force-delete');
    });

    Route::middleware(['permission:operational.records.view'])->group(function () {
        Route::get('/records/{record}', [RecordController::class, 'show'])->name('records.show');
    });

    // Maintenance (Pemeliharaan) operational routes with permissions
    Route::middleware(['permission:operational.maintenances.view'])->group(function () {
        Route::get('/maintenances', [MaintenanceController::class, 'index'])->name('maintenances.index');
        Route::get('/maintenances/data', [MaintenanceController::class, 'getData'])->name('maintenances.data');
    });

    Route::middleware(['permission:operational.maintenances.create'])->group(function () {
        Route::get('/maintenances/create', [MaintenanceController::class, 'create'])->name('maintenances.create');
        Route::post('/maintenances', [MaintenanceController::class, 'store'])->name('maintenances.store');
    });

    Route::middleware(['permission:operational.maintenances.edit'])->group(function () {
        Route::put('/maintenances/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenances.update');
        Route::delete('/maintenances/details/{detail}', [MaintenanceController::class, 'deleteDetail'])->name('maintenances.detail.destroy');
        Route::delete('/maintenances/notes/{note}', [MaintenanceController::class, 'deleteNote'])->name('maintenances.note.destroy');
    });

    Route::middleware(['permission:operational.maintenances.approve'])->group(function () {
        Route::post('/maintenances/{maintenance}/status', [MaintenanceController::class, 'status'])->name('maintenances.status');
        Route::post('/maintenances/{maintenance}/confirm', [MaintenanceController::class, 'confirm'])->name('maintenances.confirm');
    });

    Route::middleware(['permission:operational.maintenances.delete'])->group(function () {
        Route::get('/maintenances/trash', [MaintenanceController::class, 'trash'])->name('maintenances.trash');
        Route::get('/maintenances/trash/data', [MaintenanceController::class, 'getTrashData'])->name('maintenances.trash.data');
        Route::delete('/maintenances/{maintenance}', [MaintenanceController::class, 'destroy'])->name('maintenances.destroy');
    });

    Route::middleware(['permission:operational.maintenances.restore'])->group(function () {
        Route::post('/maintenances/{maintenance}/restore', [MaintenanceController::class, 'restore'])->withTrashed()->name('maintenances.restore');
    });

    Route::middleware(['permission:operational.maintenances.force_delete'])->group(function () {
        Route::delete('/maintenances/{maintenance}/force-delete', [MaintenanceController::class, 'forceDelete'])->withTrashed()->name('maintenances.force-delete');
    });

    Route::middleware(['permission:operational.maintenances.view'])->group(function () {
        Route::get('/maintenances/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenances.show');
    });

    // Reports (Rekap Laporan) routes with permissions
    Route::middleware(['permission:reports.daily.view'])->group(function () {
        Route::get('/report/daily', [ReportController::class, 'daily'])->name('report.daily');
    });
    Route::middleware(['permission:reports.daily.export'])->group(function () {
        Route::get('/report/daily/pdf', [ReportController::class, 'dailyPdf'])->name('report.daily.pdf');
    });
    Route::middleware(['permission:reports.monthly.view'])->group(function () {
        Route::get('/report/monthly', [ReportController::class, 'monthly'])->name('report.monthly');
    });
    Route::middleware(['permission:reports.monthly.export'])->group(function () {
        Route::get('/report/monthly/pdf', [ReportController::class, 'monthlyPdf'])->name('report.monthly.pdf');
    });
    Route::middleware(['permission:reports.generator.view'])->group(function () {
        Route::get('/report/generator', [ReportGeneratorController::class, 'index'])->name('report.generator');
    });
    Route::middleware(['permission:reports.generator.export'])->group(function () {
        Route::get('/report/generator/excel', [ReportGeneratorController::class, 'excel'])->name('report.generator.excel');
    });
    Route::middleware(['permission:reports.analysis_monthly.view'])->group(function () {
        Route::get('/report/analysis/monthly', [ReportController::class, 'analysisMonthly'])->name('report.analysis.monthly');
    });
    Route::middleware(['permission:reports.analysis_monthly.export'])->group(function () {
        Route::get('/report/analysis/monthly/pdf', [ReportController::class, 'analysisMonthlyPdf'])->name('report.analysis.monthly.pdf');
        Route::get('/report/analysis/monthly/excel', [ReportController::class, 'analysisMonthlyExcel'])->name('report.analysis.monthly.excel');
    });
    Route::middleware(['permission:reports.maintenances.daily.view'])->group(function () {
        Route::get('/report/maintenances/daily', [ReportController::class, 'maintenancesDaily'])->name('report.maintenances.daily');
    });
    Route::middleware(['permission:reports.maintenances.daily.export'])->group(function () {
        Route::get('/report/maintenances/daily/pdf', [ReportController::class, 'maintenancesDailyPdf'])->name('report.maintenances.daily.pdf');
    });
    Route::middleware(['permission:reports.maintenances.monthly.view'])->group(function () {
        Route::get('/report/maintenances/monthly', [ReportController::class, 'maintenancesMonthly'])->name('report.maintenances.monthly');
    });
    Route::middleware(['permission:reports.maintenances.monthly.export'])->group(function () {
        Route::get('/report/maintenances/monthly/pdf', [ReportController::class, 'maintenancesMonthlyPdf'])->name('report.maintenances.monthly.pdf');
    });
    Route::middleware(['permission:reports.analysis_maintenances_monthly.view'])->group(function () {
        Route::get('/report/analysis/maintenances/monthly', [ReportController::class, 'analysisMaintenancesMonthly'])->name('report.analysis.maintenances.monthly');
    });
    Route::middleware(['permission:reports.analysis_maintenances_monthly.export'])->group(function () {
        Route::get('/report/analysis/maintenances/monthly/pdf', [ReportController::class, 'analysisMaintenancesMonthlyPdf'])->name('report.analysis.maintenances.monthly.pdf');
        Route::get('/report/analysis/maintenances/monthly/excel', [ReportController::class, 'analysisMaintenancesMonthlyExcel'])->name('report.analysis.maintenances.monthly.excel');
    });
    Route::middleware(['permission:reports.maintenances.year.view'])->group(function () {
        Route::get('/report/maintenances/year', [ReportController::class, 'maintenancesYear'])->name('report.maintenances.year');
    });
    Route::middleware(['permission:reports.maintenances.year.export'])->group(function () {
        Route::get('/report/maintenances/year/pdf', [ReportController::class, 'maintenancesYearPdf'])->name('report.maintenances.year.pdf');
        Route::get('/report/maintenances/year/excel', [ReportController::class, 'maintenancesYearExcel'])->name('report.maintenances.year.excel');
    });

    // Destination master data routes with permissions
    Route::middleware(['permission:master.destinations.view'])->group(function () {
        Route::get('/destinations', [DestinationController::class, 'index'])->name('destinations.index');
        Route::get('/destinations/data', [DestinationController::class, 'getData'])->name('destinations.data');
    });

    Route::middleware(['permission:master.destinations.create'])->group(function () {
        Route::post('/destinations', [DestinationController::class, 'store'])->name('destinations.store');
    });

    Route::middleware(['permission:master.destinations.edit'])->group(function () {
        Route::put('/destinations/{destination}', [DestinationController::class, 'update'])->name('destinations.update');
    });

    Route::middleware(['permission:master.destinations.delete'])->group(function () {
        Route::get('/destinations/trash', [DestinationController::class, 'trash'])->name('destinations.trash');
        Route::get('/destinations/trash/data', [DestinationController::class, 'getTrashData'])->name('destinations.trash.data');
        Route::delete('/destinations/{destination}', [DestinationController::class, 'destroy'])->name('destinations.destroy');
    });

    Route::middleware(['permission:master.destinations.restore'])->group(function () {
        Route::post('/destinations/{destination}/restore', [DestinationController::class, 'restore'])->withTrashed()->name('destinations.restore');
    });

    Route::middleware(['permission:master.destinations.force_delete'])->group(function () {
        Route::delete('/destinations/{destination}/force-delete', [DestinationController::class, 'forceDelete'])->withTrashed()->name('destinations.force-delete');
    });

    Route::middleware(['permission:master.destinations.view'])->group(function () {
        Route::get('/destinations/{destination}', [DestinationController::class, 'show'])->name('destinations.show');
    });

    // Type maintenance master data routes with permissions
    Route::middleware(['permission:master.type_maintenances.view'])->group(function () {
        Route::get('/type-maintenances', [TypeMaintenanceController::class, 'index'])->name('type-maintenances.index');
        Route::get('/type-maintenances/data', [TypeMaintenanceController::class, 'getData'])->name('type-maintenances.data');
    });

    Route::middleware(['permission:master.type_maintenances.create'])->group(function () {
        Route::post('/type-maintenances', [TypeMaintenanceController::class, 'store'])->name('type-maintenances.store');
        Route::post('/type-maintenances/import', [TypeMaintenanceController::class, 'import'])->name('type-maintenances.import');
    });

    Route::middleware(['permission:master.type_maintenances.edit'])->group(function () {
        Route::put('/type-maintenances/{typeMaintenance}', [TypeMaintenanceController::class, 'update'])->name('type-maintenances.update');
    });

    Route::middleware(['permission:master.type_maintenances.delete'])->group(function () {
        Route::get('/type-maintenances/trash', [TypeMaintenanceController::class, 'trash'])->name('type-maintenances.trash');
        Route::get('/type-maintenances/trash/data', [TypeMaintenanceController::class, 'getTrashData'])->name('type-maintenances.trash.data');
        Route::delete('/type-maintenances/{typeMaintenance}', [TypeMaintenanceController::class, 'destroy'])->name('type-maintenances.destroy');
    });

    Route::middleware(['permission:master.type_maintenances.restore'])->group(function () {
        Route::post('/type-maintenances/{typeMaintenance}/restore', [TypeMaintenanceController::class, 'restore'])->withTrashed()->name('type-maintenances.restore');
    });

    Route::middleware(['permission:master.type_maintenances.force_delete'])->group(function () {
        Route::delete('/type-maintenances/{typeMaintenance}/force-delete', [TypeMaintenanceController::class, 'forceDelete'])->withTrashed()->name('type-maintenances.force-delete');
    });

    Route::middleware(['permission:master.type_maintenances.view'])->group(function () {
        Route::get('/type-maintenances/{typeMaintenance}', [TypeMaintenanceController::class, 'show'])->name('type-maintenances.show');
    });

    // Fuel type master data routes with permissions
    Route::middleware(['permission:master.fuel_types.view'])->group(function () {
        Route::get('/fuel-types', [FuelTypeController::class, 'index'])->name('fuel-types.index');
        Route::get('/fuel-types/data', [FuelTypeController::class, 'getData'])->name('fuel-types.data');
    });

    Route::middleware(['permission:master.fuel_types.create'])->group(function () {
        Route::post('/fuel-types', [FuelTypeController::class, 'store'])->name('fuel-types.store');
    });

    Route::middleware(['permission:master.fuel_types.edit'])->group(function () {
        Route::put('/fuel-types/{fuelType}', [FuelTypeController::class, 'update'])->name('fuel-types.update');
    });

    Route::middleware(['permission:master.fuel_types.delete'])->group(function () {
        Route::get('/fuel-types/trash', [FuelTypeController::class, 'trash'])->name('fuel-types.trash');
        Route::get('/fuel-types/trash/data', [FuelTypeController::class, 'getTrashData'])->name('fuel-types.trash.data');
        Route::delete('/fuel-types/{fuelType}', [FuelTypeController::class, 'destroy'])->name('fuel-types.destroy');
    });

    Route::middleware(['permission:master.fuel_types.restore'])->group(function () {
        Route::post('/fuel-types/{fuelType}/restore', [FuelTypeController::class, 'restore'])->withTrashed()->name('fuel-types.restore');
    });

    Route::middleware(['permission:master.fuel_types.force_delete'])->group(function () {
        Route::delete('/fuel-types/{fuelType}/force-delete', [FuelTypeController::class, 'forceDelete'])->withTrashed()->name('fuel-types.force-delete');
    });

    Route::middleware(['permission:master.fuel_types.view'])->group(function () {
        Route::get('/fuel-types/{fuelType}', [FuelTypeController::class, 'show'])->name('fuel-types.show');
    });

    // Vehicle type master data routes with permissions
    Route::middleware(['permission:master.vehicle_types.view'])->group(function () {
        Route::get('/vehicle-types', [VehicleTypeController::class, 'index'])->name('vehicle-types.index');
        Route::get('/vehicle-types/data', [VehicleTypeController::class, 'getData'])->name('vehicle-types.data');
    });

    Route::middleware(['permission:master.vehicle_types.create'])->group(function () {
        Route::post('/vehicle-types', [VehicleTypeController::class, 'store'])->name('vehicle-types.store');
    });

    Route::middleware(['permission:master.vehicle_types.edit'])->group(function () {
        Route::put('/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'update'])->name('vehicle-types.update');
    });

    Route::middleware(['permission:master.vehicle_types.delete'])->group(function () {
        Route::get('/vehicle-types/trash', [VehicleTypeController::class, 'trash'])->name('vehicle-types.trash');
        Route::get('/vehicle-types/trash/data', [VehicleTypeController::class, 'getTrashData'])->name('vehicle-types.trash.data');
        Route::delete('/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'destroy'])->name('vehicle-types.destroy');
    });

    Route::middleware(['permission:master.vehicle_types.restore'])->group(function () {
        Route::post('/vehicle-types/{vehicleType}/restore', [VehicleTypeController::class, 'restore'])->withTrashed()->name('vehicle-types.restore');
    });

    Route::middleware(['permission:master.vehicle_types.force_delete'])->group(function () {
        Route::delete('/vehicle-types/{vehicleType}/force-delete', [VehicleTypeController::class, 'forceDelete'])->withTrashed()->name('vehicle-types.force-delete');
    });

    Route::middleware(['permission:master.vehicle_types.view'])->group(function () {
        Route::get('/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'show'])->name('vehicle-types.show');
    });

    // Vehicle brand master data routes with permissions
    Route::middleware(['permission:master.vehicle_brands.view'])->group(function () {
        Route::get('/vehicle-brands', [VehicleBrandController::class, 'index'])->name('vehicle-brands.index');
        Route::get('/vehicle-brands/data', [VehicleBrandController::class, 'getData'])->name('vehicle-brands.data');
    });

    Route::middleware(['permission:master.vehicle_brands.create'])->group(function () {
        Route::post('/vehicle-brands', [VehicleBrandController::class, 'store'])->name('vehicle-brands.store');
    });

    Route::middleware(['permission:master.vehicle_brands.edit'])->group(function () {
        Route::put('/vehicle-brands/{vehicleBrand}', [VehicleBrandController::class, 'update'])->name('vehicle-brands.update');
    });

    Route::middleware(['permission:master.vehicle_brands.delete'])->group(function () {
        Route::get('/vehicle-brands/trash', [VehicleBrandController::class, 'trash'])->name('vehicle-brands.trash');
        Route::get('/vehicle-brands/trash/data', [VehicleBrandController::class, 'getTrashData'])->name('vehicle-brands.trash.data');
        Route::delete('/vehicle-brands/{vehicleBrand}', [VehicleBrandController::class, 'destroy'])->name('vehicle-brands.destroy');
    });

    Route::middleware(['permission:master.vehicle_brands.restore'])->group(function () {
        Route::post('/vehicle-brands/{vehicleBrand}/restore', [VehicleBrandController::class, 'restore'])->withTrashed()->name('vehicle-brands.restore');
    });

    Route::middleware(['permission:master.vehicle_brands.force_delete'])->group(function () {
        Route::delete('/vehicle-brands/{vehicleBrand}/force-delete', [VehicleBrandController::class, 'forceDelete'])->withTrashed()->name('vehicle-brands.force-delete');
    });

    Route::middleware(['permission:master.vehicle_brands.view'])->group(function () {
        Route::get('/vehicle-brands/{vehicleBrand}', [VehicleBrandController::class, 'show'])->name('vehicle-brands.show');
    });

    // Generic FilePond routes
    Route::post('/uploads/process', [FileUploadController::class, 'process'])->name('uploads.process');
    Route::post('/uploads/attachments/process', [FileUploadController::class, 'processAttachment'])->name('uploads.attachments.process');
    Route::delete('/uploads/revert', [FileUploadController::class, 'revert'])->name('uploads.revert');
    Route::get('/uploads/load', [FileUploadController::class, 'load'])->name('uploads.load');
    Route::get('/uploads/proxy', [FileUploadController::class, 'proxy'])->name('uploads.proxy');

    // Form demo routes
    Route::get('/form-demo', [FormDemoController::class, 'index'])->name('form-demo.index');
    Route::post('/form-demo', [FormDemoController::class, 'store'])->name('form-demo.store');
});
