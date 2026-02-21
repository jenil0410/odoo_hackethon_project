<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\VehicleRegistryController;
use App\Http\Controllers\DriverController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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
});

require __DIR__.'/auth.php';
