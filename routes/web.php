<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
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
});

require __DIR__.'/auth.php';
