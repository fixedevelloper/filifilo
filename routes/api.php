<?php

use App\Http\Controllers\API\AuthApiController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::post('/authenticate', [AuthApiController::class, 'login']);
Route::post('/register', [AuthApiController::class, 'register']);
Route::middleware('customer_jwt')->group(function () {
    Route::get('/search', [StoreController::class, 'search']);
    Route::get('/stores', [StoreController::class, 'stores']);
    Route::get('/categories', [StoreController::class, 'categories']);
    Route::get('/products/{store_id}', [StoreController::class, 'products']);
    Route::get('/stores/{id}', [StoreController::class, 'getDeatailStore']);
    Route::post('/orders', [OrderController::class, 'createOrder']);
    Route::get('/orders', [OrderController::class, 'orders']);
    Route::get('/orders/{id}', [OrderController::class, 'orderByID']);
    Route::get('/profile', [AuthApiController::class, 'profile']);
    Route::post('/profile', [AuthApiController::class, 'updateProfile']);
    Route::post('/change_password', [AuthApiController::class, 'changePassword']);
});
