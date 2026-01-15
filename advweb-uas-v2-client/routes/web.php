<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

// Auth Routes (Public)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('api.auth')->group(function () {
    // POS Routes
    Route::get('/', [PosController::class, 'index']);
    Route::get('/pos/history', [PosController::class, 'history']);
    Route::post('/pos/checkout', [PosController::class, 'checkout']);
    Route::post('/pos/print/{id}', [PosController::class, 'print']);

    // Settings Routes
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');

    // Admin Routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', [DashboardController::class, 'index']);
        
        // Menu Management
        Route::post('/admin/menu', [MenuController::class, 'store']);
        Route::post('/admin/menu/{id}', [MenuController::class, 'update']); // Using POST with _method for file uploads
        Route::delete('/admin/menu/{id}', [MenuController::class, 'destroy']);

        // User Management
        Route::get('/admin/users', [UserController::class, 'index']);
        Route::post('/admin/users', [UserController::class, 'store']);
        Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
    });
});
