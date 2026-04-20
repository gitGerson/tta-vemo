<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\MobileController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductEsbController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FormDemoController;
use App\Http\Controllers\FileUploadController;

Route::get('/', function () {
    // return to login
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

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

    // Product ESB sync routes with permissions
    Route::middleware(['permission:master.products_esb.view'])->group(function () {
        Route::get('/products-esb', [ProductEsbController::class, 'index'])->name('products-esb.index');
        Route::get('/products-esb/data', [ProductEsbController::class, 'getData'])->name('products-esb.data');
    });

    Route::middleware(['permission:master.products_esb.sync'])->group(function () {
        Route::post('/products-esb/sync-page', [ProductEsbController::class, 'syncSinglePage'])->name('products-esb.sync-page');
        Route::post('/products-esb/sync-all', [ProductEsbController::class, 'syncAll'])->name('products-esb.sync-all');
    });

    // Employee management routes with permissions
    Route::middleware(['permission:master.employees.view'])->group(function () {
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/data', [EmployeeController::class, 'getData'])->name('employees.data');
    });

    Route::middleware(['permission:master.employees.sync'])->group(function () {
        Route::post('/employees/sync-jpayroll', [EmployeeController::class, 'syncJPayroll'])->name('employees.sync-jpayroll');
    });

    // Mobile routes
    Route::get('/mobile', [MobileController::class, 'index'])->name('mobile.index');

    // Generic FilePond routes
    Route::post('/uploads/process', [FileUploadController::class, 'process'])->name('uploads.process');
    Route::delete('/uploads/revert', [FileUploadController::class, 'revert'])->name('uploads.revert');
    Route::get('/uploads/load', [FileUploadController::class, 'load'])->name('uploads.load');

    // Form demo routes
    Route::get('/form-demo', [FormDemoController::class, 'index'])->name('form-demo.index');
    Route::post('/form-demo', [FormDemoController::class, 'store'])->name('form-demo.store');
});
