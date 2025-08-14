<?php

use App\Events\TransporterLocationUpdated;
use App\Http\Controllers\API\AuthApiController;
use App\Http\Controllers\API\ManagerController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\StoreController;
use App\Http\Controllers\API\TransporterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// routes/api.php
Route::post('/broadcasting/auth', function () {
    return response()->json(['auth' => true]);
});

Route::post('/broadcastin/auth', [AuthApiController::class, 'authenticateBroacast']);
Route::post('login', [AuthApiController::class, 'login'])->name('login');
Route::post('register', [AuthApiController::class, 'register']);
Route::get('/send-notif', [NotificationController::class, 'send']);
Route::post('/driver/location', [TransporterController::class, 'updateTransporterPosition']);
Route::middleware('auth:api')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/search', [StoreController::class, 'search']);
    Route::get('/stores', [StoreController::class, 'stores']);
    Route::get('/categories', [StoreController::class, 'categories']);
    Route::get('/ingredients', [StoreController::class, 'categories']);
    Route::get('/products/{store_id}', [StoreController::class, 'products']);
    Route::get('/stores/{id}', [StoreController::class, 'getDeatailStore']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::get('/orders', [OrderController::class, 'orders']);
    Route::get('/orders/transporters', [TransporterController::class, 'orders']);
    Route::get('/orders/transporters/my', [TransporterController::class, 'myOrders']);
    Route::put('/drivers/order/{id}/status', [TransporterController::class, 'updateStatus']);
    Route::get('/orders/{id}', [OrderController::class, 'orderByID']);
    Route::get('/profile', [AuthApiController::class, 'profile']);
    Route::post('/profile', [AuthApiController::class, 'updateProfile']);
    Route::post('/change_password', [AuthApiController::class, 'changePassword']);

    Route::get('vendors/store', [ManagerController::class, 'getStore']);
    Route::get('vendors/products', [ManagerController::class, 'products']);
    Route::get('vendors/products/{id}', [ManagerController::class, 'products']);
    Route::get('vendors/orders', [ManagerController::class, 'orders']);
    Route::post('vendors/products', [ManagerController::class, 'createProduct']);
    Route::post('vendors/orders/status', [ManagerController::class, 'updateStatus']);
    Route::get('vendors/orders/{id}', [OrderController::class, 'orderByID']);
    Route::get('vendors/revenue-details', [ManagerController::class, 'revenueDetails']);
    Route::get('vendors/chart-data', [ManagerController::class, 'getChartData']);
});


