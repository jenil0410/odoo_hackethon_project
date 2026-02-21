<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\FuelLogController;
use App\Http\Controllers\MaintenanceLogController;
use App\Http\Controllers\OperationalAnalyticsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\VehicleRegistryController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('user', UserController::class);
    Route::get('user/delete/{id}', [UserController::class, 'destroy'])->name('user.delete');
    Route::get('user/{id}/permissions', [UserPermissionController::class, 'edit'])->name('user.permissions');
    Route::put('user/{id}/permissions', [UserPermissionController::class, 'update'])->name('user.permissions-update');

    Route::resource('role', RoleController::class);
    Route::get('role/delete/{id}', [RoleController::class, 'destroy'])->name('role.delete');

    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

    Route::get('vehicle-registry', [VehicleRegistryController::class, 'index'])->name('vehicle-registry.index');
    Route::post('vehicle-registry', [VehicleRegistryController::class, 'store'])->name('vehicle-registry.store');
    Route::put('vehicle-registry/{id}', [VehicleRegistryController::class, 'update'])->name('vehicle-registry.update');
    Route::get('vehicle-registry/fetch/{id}', [VehicleRegistryController::class, 'fetch'])->name('vehicle-registry.fetch');
    Route::get('vehicle-registry/delete/{id}', [VehicleRegistryController::class, 'destroy'])->name('vehicle-registry.delete');

    Route::get('driver', [DriverController::class, 'index'])->name('driver.index');
    Route::post('driver', [DriverController::class, 'store'])->name('driver.store');
    Route::put('driver/{id}', [DriverController::class, 'update'])->name('driver.update');
    Route::get('driver/fetch/{id}', [DriverController::class, 'fetch'])->name('driver.fetch');
    Route::get('driver/delete/{id}', [DriverController::class, 'destroy'])->name('driver.delete');
    Route::get('driver/{id}/can-assign', [DriverController::class, 'canAssign'])->name('driver.can-assign');

    Route::get('trip', [TripController::class, 'index'])->name('trip.index');
    Route::post('trip', [TripController::class, 'store'])->name('trip.store');
    Route::put('trip/{id}', [TripController::class, 'update'])->name('trip.update');
    Route::post('trip/{id}/status', [TripController::class, 'changeStatus'])->name('trip.status');
    Route::get('trip/fetch/{id}', [TripController::class, 'fetch'])->name('trip.fetch');
    Route::get('trip/delete/{id}', [TripController::class, 'destroy'])->name('trip.delete');

    Route::get('maintenance-log', [MaintenanceLogController::class, 'index'])->name('maintenance-log.index');
    Route::post('maintenance-log', [MaintenanceLogController::class, 'store'])->name('maintenance-log.store');
    Route::put('maintenance-log/{id}', [MaintenanceLogController::class, 'update'])->name('maintenance-log.update');
    Route::post('maintenance-log/{id}/complete', [MaintenanceLogController::class, 'markCompleted'])->name('maintenance-log.complete');
    Route::get('maintenance-log/fetch/{id}', [MaintenanceLogController::class, 'fetch'])->name('maintenance-log.fetch');
    Route::get('maintenance-log/delete/{id}', [MaintenanceLogController::class, 'destroy'])->name('maintenance-log.delete');

    Route::get('fuel-log', [FuelLogController::class, 'index'])->name('fuel-log.index');
    Route::post('fuel-log', [FuelLogController::class, 'store'])->name('fuel-log.store');
    Route::put('fuel-log/{id}', [FuelLogController::class, 'update'])->name('fuel-log.update');
    Route::get('fuel-log/fetch/{id}', [FuelLogController::class, 'fetch'])->name('fuel-log.fetch');
    Route::get('fuel-log/delete/{id}', [FuelLogController::class, 'destroy'])->name('fuel-log.delete');

    Route::get('analytics', [OperationalAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/export/payroll/csv', [OperationalAnalyticsController::class, 'exportPayrollCsv'])->name('analytics.export.payroll.csv');
    Route::get('analytics/export/payroll/pdf', [OperationalAnalyticsController::class, 'exportPayrollPdf'])->name('analytics.export.payroll.pdf');
    Route::get('analytics/export/health/csv', [OperationalAnalyticsController::class, 'exportHealthCsv'])->name('analytics.export.health.csv');
    Route::get('analytics/export/health/pdf', [OperationalAnalyticsController::class, 'exportHealthPdf'])->name('analytics.export.health.pdf');
});

require __DIR__.'/auth.php';
