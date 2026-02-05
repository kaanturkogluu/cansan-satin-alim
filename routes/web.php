<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Request Routes
    Route::get('/requests', [RequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [RequestController::class, 'create'])->name('requests.create');
    Route::get('/requests/search-images', [RequestController::class, 'searchImages'])->name('requests.search-images');
    Route::post('/requests', [RequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{requestForm}', [RequestController::class, 'show'])->name('requests.show');

    Route::get('/approvals', [RequestController::class, 'approvals'])->name('requests.approvals');
    Route::post('/requests/{requestForm}/approve', [RequestController::class, 'approve'])->name('requests.approve');
    Route::post('/requests/{requestForm}/reject', [RequestController::class, 'reject'])->name('requests.reject');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/requests', [App\Http\Controllers\AdminController::class, 'requests'])->name('admin.requests');
    Route::get('/admin/requests/{requestForm}/edit', [App\Http\Controllers\AdminController::class, 'editRequest'])->name('admin.requests.edit');
    Route::put('/admin/requests/{requestForm}', [App\Http\Controllers\AdminController::class, 'updateRequest'])->name('admin.requests.update');
    Route::get('/admin/roles', [App\Http\Controllers\AdminController::class, 'roles'])->name('admin.roles');
    Route::get('/admin/roles/create', [App\Http\Controllers\AdminController::class, 'createRole'])->name('admin.roles.create');
    Route::post('/admin/roles', [App\Http\Controllers\AdminController::class, 'storeRole'])->name('admin.roles.store');
    Route::get('/admin/roles/{role}/edit', [App\Http\Controllers\AdminController::class, 'editRole'])->name('admin.roles.edit');
    Route::put('/admin/roles/{role}', [App\Http\Controllers\AdminController::class, 'updateRole'])->name('admin.roles.update');
    Route::delete('/admin/roles/{role}', [App\Http\Controllers\AdminController::class, 'destroyRole'])->name('admin.roles.destroy');
    Route::get('/admin/departments', [App\Http\Controllers\AdminController::class, 'departments'])->name('admin.departments');
    Route::get('/admin/departments/create', [App\Http\Controllers\AdminController::class, 'createDepartment'])->name('admin.departments.create');
    Route::post('/admin/departments', [App\Http\Controllers\AdminController::class, 'storeDepartment'])->name('admin.departments.store');
    Route::get('/admin/departments/{department}/edit', [App\Http\Controllers\AdminController::class, 'editDepartment'])->name('admin.departments.edit');
    Route::put('/admin/departments/{department}', [App\Http\Controllers\AdminController::class, 'updateDepartment'])->name('admin.departments.update');
    Route::delete('/admin/departments/{department}', [App\Http\Controllers\AdminController::class, 'destroyDepartment'])->name('admin.departments.destroy');
    Route::get('/admin/units', [App\Http\Controllers\AdminController::class, 'units'])->name('admin.units');
    Route::get('/admin/units/create', [App\Http\Controllers\AdminController::class, 'createUnit'])->name('admin.units.create');
    Route::post('/admin/units', [App\Http\Controllers\AdminController::class, 'storeUnit'])->name('admin.units.store');
    Route::get('/admin/units/{unit}/edit', [App\Http\Controllers\AdminController::class, 'editUnit'])->name('admin.units.edit');
    Route::put('/admin/units/{unit}', [App\Http\Controllers\AdminController::class, 'updateUnit'])->name('admin.units.update');
    Route::delete('/admin/units/{unit}', [App\Http\Controllers\AdminController::class, 'destroyUnit'])->name('admin.units.destroy');
    Route::get('/admin/users', [App\Http\Controllers\AdminController::class, 'users'])->name('admin.users');
    Route::get('/admin/users/create', [App\Http\Controllers\AdminController::class, 'createUser'])->name('admin.users.create');
    Route::post('/admin/users', [App\Http\Controllers\AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::get('/admin/users/{user}/edit', [App\Http\Controllers\AdminController::class, 'editUser'])->name('admin.users.edit');
    Route::put('/admin/users/{user}', [App\Http\Controllers\AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::delete('/admin/users/{user}', [App\Http\Controllers\AdminController::class, 'destroyUser'])->name('admin.users.destroy');
});

require __DIR__ . '/auth.php';
