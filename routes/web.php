<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DestinationController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MaintenancesController;
use App\Http\Controllers\MaintenancesDetailsController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TypeMaintenanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return to login
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Route group middleware for authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // FilePond file upload routes
    Route::post('/uploads/process', [FileUploadController::class, 'process'])->name('uploads.process');
    Route::delete('/uploads/revert', [FileUploadController::class, 'revert'])->name('uploads.revert');
    Route::get('/uploads/load', [FileUploadController::class, 'load'])->name('uploads.load');

    // User management routes with permissions
    Route::middleware(['permission:management.users.create'])->group(function () {
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
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
        Route::delete('/users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
    });

    Route::middleware(['permission:management.users.restore'])->group(function () {
        Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
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

    // User role assignment routes
    Route::middleware(['permission:management.users.edit'])->group(function () {
        Route::get('/users/{user}/roles', [UserRoleController::class, 'show'])->name('users.roles');
        Route::put('/users/{user}/roles', [UserRoleController::class, 'update'])->name('users.roles.update');
        Route::post('/users/{user}/roles/assign', [UserRoleController::class, 'assignRole'])->name('users.roles.assign');
        Route::delete('/users/{user}/roles/remove', [UserRoleController::class, 'removeRole'])->name('users.roles.remove');
    });

    // Image management routes with permissions
    Route::middleware(['permission:images.create'])->group(function () {
        Route::get('/images/create', [ImageController::class, 'create'])->name('images.create');
        Route::post('/images', [ImageController::class, 'store'])->name('images.store');
    });

    Route::middleware(['permission:images.view'])->group(function () {
        Route::get('/images', [ImageController::class, 'index'])->name('images.index');
        Route::get('/images/data', [ImageController::class, 'getData'])->name('images.data');
        Route::get('/images/{image}', [ImageController::class, 'show'])->name('images.show');
    });

    Route::middleware(['permission:images.edit'])->group(function () {
        Route::get('/images/{image}/edit', [ImageController::class, 'edit'])->name('images.edit');
        Route::put('/images/{image}', [ImageController::class, 'update'])->name('images.update');
        Route::patch('/images/{image}', [ImageController::class, 'update']);
    });

    Route::middleware(['permission:images.delete'])->group(function () {
        Route::get('/images/trash', [ImageController::class, 'trash'])->name('images.trash');
        Route::get('/images/trash/data', [ImageController::class, 'getTrashData'])->name('images.trash-data');
        Route::delete('/images/{image}', [ImageController::class, 'destroy'])->name('images.destroy');
        Route::delete('/images/{id}/force-delete', [ImageController::class, 'forceDelete'])->name('images.force-delete');
    });

    Route::middleware(['permission:images.restore'])->group(function () {
        Route::post('/images/{id}/restore', [ImageController::class, 'restore'])->name('images.restore');
    });

    // Category management routes with permissions
    Route::middleware(['permission:master.categories.delete'])->group(function () {
        Route::get('/categories/trash', [CategoryController::class, 'trash'])->name('categories.trash');
        Route::get('/categories/trash/data', [CategoryController::class, 'getTrashData'])->name('categories.trash.data');
    });

    Route::middleware(['permission:master.categories.create'])->group(function () {
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    });

    Route::middleware(['permission:master.categories.view'])->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/data', [CategoryController::class, 'getData'])->name('categories.data');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    });

    Route::middleware(['permission:master.categories.edit'])->group(function () {
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
    });

    Route::middleware(['permission:master.categories.delete'])->group(function () {
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::delete('/categories/{id}/force-delete', [CategoryController::class, 'forceDelete'])->name('categories.force-delete');
    });

    Route::middleware(['permission:master.categories.restore'])->group(function () {
        Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
    });

    // Product management routes with permissions
    Route::middleware(['permission:master.products.delete'])->group(function () {
        Route::get('/products/trash', [ProductController::class, 'trash'])->name('products.trash');
        Route::get('/products/trash/data', [ProductController::class, 'getTrashData'])->name('products.trash.data');
    });

    Route::middleware(['permission:master.products.create'])->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    });

    Route::middleware(['permission:master.products.view'])->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/data', [ProductController::class, 'getData'])->name('products.data');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
    });

    Route::middleware(['permission:master.products.edit'])->group(function () {
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::patch('/products/{product}', [ProductController::class, 'update']);
    });

    Route::middleware(['permission:master.products.delete'])->group(function () {
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force-delete');
    });

    Route::middleware(['permission:master.products.restore'])->group(function () {
        Route::post('/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');
    });

    // Company
    Route::middleware(['permission:master.companies.view'])->group(function () {
        Route::get('/company', [CompanyController::class, 'index'])->name('company.index');
        Route::get('/company/data', [CompanyController::class, 'getData'])->name('company.data');
    });
    Route::middleware(['permission:master.companies.create'])->group(function () {
        Route::post('/company', [CompanyController::class, 'store'])->name('company.store');
    });
    Route::middleware(['permission:master.companies.edit'])->group(function () {
        Route::put('/company/{company}', [CompanyController::class, 'update'])->name('company.update');
    });
    Route::middleware(['permission:master.companies.delete'])->group(function () {
        Route::delete('/company/{company}', [CompanyController::class, 'destroy'])->name('company.destroy');
    });

    // Driver
    Route::middleware(['permission:master.drivers.view'])->group(function () {
        Route::get('/driver', [DriverController::class, 'index'])->name('driver.index');
        Route::get('/driver/data', [DriverController::class, 'getData'])->name('driver.data');
        Route::get('/driver/emp-info', [DriverController::class, 'getEmpInfo'])->name('driver.empInfo');
    });
    Route::middleware(['permission:master.drivers.create'])->group(function () {
        Route::post('/driver', [DriverController::class, 'store'])->name('driver.store');
    });
    Route::middleware(['permission:master.drivers.edit'])->group(function () {
        Route::put('/driver/{driver}', [DriverController::class, 'update'])->name('driver.update');
    });
    Route::middleware(['permission:master.drivers.delete'])->group(function () {
        Route::delete('/driver/{driver}', [DriverController::class, 'destroy'])->name('driver.destroy');
    });

    // Vehicle
    Route::middleware(['permission:master.vehicles.view'])->group(function () {
        Route::get('/vehicle', [VehicleController::class, 'index'])->name('vehicle.index');
        Route::get('/vehicle/data', [VehicleController::class, 'getData'])->name('vehicle.data');
    });
    Route::middleware(['permission:master.vehicles.create'])->group(function () {
        Route::post('/vehicle', [VehicleController::class, 'store'])->name('vehicle.store');
    });
    Route::middleware(['permission:master.vehicles.edit'])->group(function () {
        Route::put('/vehicle/{vehicle}', [VehicleController::class, 'update'])->name('vehicle.update');
    });
    Route::middleware(['permission:master.vehicles.delete'])->group(function () {
        Route::delete('/vehicle/{vehicle}', [VehicleController::class, 'destroy'])->name('vehicle.destroy');
    });

    // Destination
    Route::middleware(['permission:master.destinations.view'])->group(function () {
        Route::get('/destination', [DestinationController::class, 'index'])->name('destination.index');
        Route::get('/destination/data', [DestinationController::class, 'getData'])->name('destination.data');
    });
    Route::middleware(['permission:master.destinations.create'])->group(function () {
        Route::post('/destination', [DestinationController::class, 'store'])->name('destination.store');
    });
    Route::middleware(['permission:master.destinations.edit'])->group(function () {
        Route::put('/destination/{destination}', [DestinationController::class, 'update'])->name('destination.update');
    });
    Route::middleware(['permission:master.destinations.delete'])->group(function () {
        Route::delete('/destination/{destination}', [DestinationController::class, 'destroy'])->name('destination.destroy');
    });

    // Type Maintenance
    Route::middleware(['permission:master.type_maintenances.view'])->group(function () {
        Route::get('/type-maintenance', [TypeMaintenanceController::class, 'index'])->name('type-maintenance.index');
        Route::get('/type-maintenance/data', [TypeMaintenanceController::class, 'getData'])->name('type-maintenance.data');
    });
    Route::middleware(['permission:master.type_maintenances.create'])->group(function () {
        Route::post('/type-maintenance', [TypeMaintenanceController::class, 'store'])->name('type-maintenance.store');
        Route::post('/type-maintenance/import', [TypeMaintenanceController::class, 'import'])->name('type-maintenance.import');
        Route::post('type_maintenances/post/response', [TypeMaintenanceController::class, 'storeResponse'])->name('type_maintenances.storeResponse');
    });
    Route::middleware(['permission:master.type_maintenances.edit'])->group(function () {
        Route::put('/type-maintenance/{type_maintenance}', [TypeMaintenanceController::class, 'update'])->name('type-maintenance.update');
    });
    Route::middleware(['permission:master.type_maintenances.delete'])->group(function () {
        Route::delete('/type-maintenance/{type_maintenance}', [TypeMaintenanceController::class, 'destroy'])->name('type-maintenance.destroy');
    });

    // Reports
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
    Route::middleware(['permission:reports.maintenances.daily.view'])->group(function () {
        Route::get('/report/maintenances/daily', [ReportController::class, 'maintenancesDaily'])->name('report.maintenances.daily');
    });
    Route::middleware(['permission:reports.maintenances.daily.export'])->group(function () {
        Route::get('/report/maintenances/daily/pdf', [ReportController::class, 'maintenancesDailyPdf'])->name('report.maintenances.daily.pdf');
    });
    Route::middleware(['permission:reports.maintenances.year.view'])->group(function () {
        Route::get('/report/maintenances/year', [ReportController::class, 'maintenancesYear'])->name('report.maintenances.year');
    });
    Route::middleware(['permission:reports.maintenances.year.export'])->group(function () {
        Route::get('/report/maintenances/year/pdf', [ReportController::class, 'maintenancesYearPdf'])->name('report.maintenances.year.pdf');
        Route::get('/report/maintenances/year/excel', [ReportController::class, 'maintenancesYearExcel'])->name('report.maintenances.year.excel');
    });

    // Maintenances
    Route::middleware(['permission:maintenances.view'])->group(function () {
        Route::get('/maintenances', [MaintenancesController::class, 'index'])->name('maintenances.index');
    });
    Route::middleware(['permission:maintenances.create'])->group(function () {
        Route::get('/maintenances/create', [MaintenancesController::class, 'create'])->name('maintenances.create');
        Route::get('/maintenances/notifications/{notification}/read', [HomeController::class, 'markServiceNotificationRead'])
            ->name('maintenances.notifications.read');
        Route::post('/maintenances', [MaintenancesController::class, 'store'])->name('maintenances.store');
    });
    Route::middleware(['permission:maintenances.edit'])->group(function () {
        Route::get('/maintenances/{maintenances}/edit', [MaintenancesController::class, 'edit'])->name('maintenances.edit');
        Route::put('/maintenances/{maintenances}', [MaintenancesController::class, 'update'])->name('maintenances.update');
        Route::patch('/maintenances/{maintenances}', [MaintenancesController::class, 'update']);
    });
    Route::middleware(['permission:maintenances.approve'])->group(function () {
        Route::post('/maintenances/{maintenances}/status', [MaintenancesController::class, 'status'])->name('maintenances.status');
        Route::post('/maintenances/{maintenances}/confirm', [MaintenancesController::class, 'confirm'])->name('maintenances.confirm');
    });
    Route::middleware(['permission:maintenances.delete'])->group(function () {
        Route::delete('/maintenances/{maintenances}', [MaintenancesController::class, 'destroy'])->name('maintenances.destroy');
    });
    Route::middleware(['permission:maintenances.view'])->group(function () {
        Route::get('/maintenances/{maintenances}', [MaintenancesController::class, 'show'])->name('maintenances.show');
    });
    Route::resource('detail_maintenances', MaintenancesDetailsController::class)->middleware(['permission:maintenances.edit']);

    // Record
    Route::middleware(['permission:record.view'])->group(function () {
        Route::get('/record', [RecordController::class, 'index'])->name('record.index');
        Route::get('/record/export', [RecordController::class, 'exportExcel'])->name('record.export');
    });
    Route::middleware(['permission:record.create'])->group(function () {
        Route::get('/record/create', [RecordController::class, 'create'])->name('record.create');
        Route::post('/record', [RecordController::class, 'store'])->name('record.store');
        Route::get('/checkDriver', [DriverController::class, 'checkDriver']);
    });
    Route::middleware(['permission:record.edit'])->group(function () {
        Route::get('/record/{record}/edit', [RecordController::class, 'edit'])->name('record.edit');
        Route::put('/record/{record}', [RecordController::class, 'update'])->name('record.update');
        Route::patch('/record/{record}', [RecordController::class, 'update']);
    });
    Route::middleware(['permission:record.delete'])->group(function () {
        Route::delete('/record/{record}', [RecordController::class, 'destroy'])->name('record.destroy');
        Route::get('/record/trash', [RecordController::class, 'record_trash'])->name('record.trash');
    });
    Route::middleware(['permission:record.restore'])->group(function () {
        Route::patch('/record/{id}/restore', [RecordController::class, 'restore'])->name('record.restore');
    });
    Route::middleware(['permission:record.force_delete'])->group(function () {
        Route::delete('/record/{id}/force-delete-confirmed', [RecordController::class, 'forceDeleteConfirmed'])->name('record.forceDeleteConfirmed');
    });
    Route::middleware(['permission:record.view'])->group(function () {
        Route::get('/record/{record}', [RecordController::class, 'show'])->name('record.show');
    });

    Route::resource('note', NoteController::class);
    // Mobile routes
    Route::get('/mobile', [MobileController::class, 'index'])->name('mobile.index');
});
