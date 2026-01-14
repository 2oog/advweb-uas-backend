<?php

use App\Http\Controllers\Admin\SalesController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PrinterController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
// Route::post('/register', [AuthController::class, 'register']); // Moved to admin only
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/password', [AuthController::class, 'updatePassword']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Public Menu Access
    Route::get('menu-items', [MenuController::class, 'index']);
    Route::get('menu-items/{id}', [MenuController::class, 'show']);

    // Admin Only Routes
    Route::middleware('role:admin')->group(function () {
        Route::post('menu-items', [MenuController::class, 'store']);
        Route::put('menu-items/{id}', [MenuController::class, 'update']);
        Route::delete('menu-items/{id}', [MenuController::class, 'destroy']);
        Route::get('/admin/sales', [SalesController::class, 'index']);
        Route::post('/register', [AuthController::class, 'register']);

        // User Management
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });

    // Employee & Admin can Order
    Route::apiResource('orders', OrderController::class);

    Route::post('/orders/{id}/print', [PrinterController::class, 'print']);
});
