<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\FormDemoController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return to login
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

// Route group middleware for authenticated users
Route::middleware(['auth'])->group(function () {
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
        Route::get('/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    });

    Route::middleware(['permission:master.companies.create'])->group(function () {
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
    });

    Route::middleware(['permission:master.companies.edit'])->group(function () {
        Route::put('/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    });

    Route::middleware(['permission:master.companies.delete'])->group(function () {
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    });

    // Generic FilePond routes
    Route::post('/uploads/process', [FileUploadController::class, 'process'])->name('uploads.process');
    Route::delete('/uploads/revert', [FileUploadController::class, 'revert'])->name('uploads.revert');
    Route::get('/uploads/load', [FileUploadController::class, 'load'])->name('uploads.load');
    Route::get('/uploads/proxy', [FileUploadController::class, 'proxy'])->name('uploads.proxy');

    // Form demo routes
    Route::get('/form-demo', [FormDemoController::class, 'index'])->name('form-demo.index');
    Route::post('/form-demo', [FormDemoController::class, 'store'])->name('form-demo.store');
});
