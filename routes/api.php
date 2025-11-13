<?php

use App\Http\Controllers\API\V2\Customer\RideController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V2\Admin\AdminController;
use App\Http\Controllers\API\V2\Admin\DashboardController;
use App\Http\Controllers\API\V2\Auth\AuthController;
use App\Http\Controllers\API\V2\Auth\PasswordController;
use App\Http\Controllers\API\V2\Customer\CustomerController;
use App\Http\Controllers\API\V2\Customer\AddressController;
use App\Http\Controllers\API\V2\Customer\PaymentMethodController;
use App\Http\Controllers\API\V2\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\API\V2\Customer\RatingController;
use App\Http\Controllers\API\V2\Merchant\MerchantController;
use App\Http\Controllers\API\V2\Merchant\StoreController;
use App\Http\Controllers\API\V2\Merchant\ProductController;
use App\Http\Controllers\API\V2\Merchant\OrderController as MerchantOrderController;
use App\Http\Controllers\API\V2\Driver\DriverController;
use App\Http\Controllers\API\V2\Driver\VehicleController;
use App\Http\Controllers\API\V2\Driver\DeliveryController;
use App\Http\Controllers\API\V2\Common\NotificationController;
use App\Http\Controllers\API\V2\Common\LoyaltyController;
use App\Http\Controllers\API\V2\Common\CategoryController;
use App\Http\Controllers\API\V2\Common\CityController;
use App\Http\Controllers\API\V2\Common\CountryController;

// ----------------- Auth -----------------
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::post('password/request', [PasswordController::class, 'requestReset']);
    Route::post('password/reset', [PasswordController::class, 'reset']);
    Route::post('change_password', [PasswordController::class, 'changePassword']);
    Route::post('phone/send_code', [PasswordController::class, 'sendCodeVerify']);
    Route::post('phone/verify', [PasswordController::class, 'verifyCode']);
    Route::get('countries', [AuthController::class, 'getCountries']);
});

Route::post('/driver/location', [DriverController::class, 'updatePosition']);

// ----------------- Admin -----------------
Route::prefix('admin')->middleware(['auth:sanctum','role:admin'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('notifications', [DashboardController::class, 'notifications']);
    Route::get('drivers', [AdminController::class, 'drivers']);
    Route::get('orders', [AdminController::class, 'orders']);
    Route::get('categories', [AdminController::class, 'categories']);
    Route::get('products', [AdminController::class, 'getProducts']);
    Route::get('orders/{id}', [CustomerOrderController::class, 'show']);
    Route::get('users', [AdminController::class, 'users']);
    Route::get('customers', [AdminController::class, 'customers']);
    Route::get('stores', [AdminController::class, 'stores']);
    Route::get('drivers/info/{id}', [AdminController::class, 'getInfoDriver']);
});

// ----------------- Customer -----------------
Route::prefix('customer')->middleware(['auth:sanctum','role:customer'])->group(function () {
    Route::get('dashboard', [CustomerController::class, 'dashboard']);
    Route::get('stores/{id}', [CustomerController::class, 'storeId']);
    Route::get('stores', [CustomerController::class, 'storeByType']);
    Route::get('products/{store_id}', [CustomerController::class, 'products']);
    Route::get('search', [CustomerController::class, 'search']);
    Route::get('profile', [CustomerController::class, 'profile']);
    Route::put('profile', [CustomerController::class, 'updateProfile']);
    Route::get('products/{id}/detail', [ProductController::class, 'show']);
    Route::get('drinks/{id}/index', [ProductController::class, 'drinks']);
    Route::get('loyalty/coupons', [LoyaltyController::class, 'applyCoupon']);
    Route::get('drivers/nearby', [DriverController::class, 'registerClientPickup']);
    Route::get('price_ride', [RideController::class, 'calculerTarif']);
    Route::post('rides', [RideController::class, 'store']);
    Route::get('rides/{id}', [RideController::class, 'getRide']);
    Route::apiResource('addresses', AddressController::class);
    Route::apiResource('payment-methods', PaymentMethodController::class);
    Route::apiResource('orders', CustomerOrderController::class);
    Route::post('orders/{order}/cancel', [CustomerOrderController::class, 'cancel']);
    Route::apiResource('ratings', RatingController::class);
});

// ----------------- Merchant -----------------
Route::prefix('merchant')->middleware(['auth:sanctum','role:merchant'])->group(function () {
    Route::get('dashboard/orders/{id}', [MerchantController::class, 'getDashboard']);
    Route::get('revenue-details/{id}', [MerchantController::class, 'revenueDetails']);
    Route::get('chart-data/{id}', [MerchantController::class, 'getChartData']);
    Route::get('profile', [MerchantController::class, 'profile']);
    Route::put('profile', [MerchantController::class, 'updateProfile']);
    Route::get('default-store', [StoreController::class, 'storeDefaut']);
    Route::apiResource('stores', StoreController::class);
    Route::get('products/{id}/index', [ProductController::class, 'index']);
    Route::post('products/{id}', [ProductController::class, 'store']);
    Route::post('products/{id}/update', [ProductController::class, 'update']);
    Route::get('drinks/{id}/index', [ProductController::class, 'drinks']);
    Route::post('drinks/{id}', [ProductController::class, 'add_drink']);
    Route::apiResource('products', ProductController::class);
    Route::get('featured_products/{id}', [ProductController::class, 'featured_products']);
    Route::get('orders/{id}/index', [MerchantOrderController::class, 'index']);
    Route::apiResource('orders', MerchantOrderController::class);
    Route::post('orders/status', [MerchantOrderController::class, 'accept']);
    Route::post('orders/{order}/reject', [MerchantOrderController::class, 'reject']);
    Route::put('orders/{order}/preparation-time', [MerchantOrderController::class, 'updatePreparationTime']);
});

// ----------------- Driver -----------------
Route::prefix('driver')->middleware(['auth:sanctum','role:driver'])->group(function () {
    Route::get('profile', [DriverController::class, 'profile']);
    Route::put('profile', [DriverController::class, 'updateProfile']);
    Route::get('summary', [DriverController::class, 'summary']);

    Route::apiResource('vehicules', VehicleController::class);
    Route::get('selected/{id}', [VehicleController::class, 'selectedVehicule']);
    Route::apiResource('deliveries', DeliveryController::class);
    Route::post('deliveries/{delivery}/accept', [DeliveryController::class, 'accept']);
    Route::put('deliveries/{delivery}/status', [DeliveryController::class, 'updateStatus']);
});

// ----------------- Common -----------------
Route::prefix('common')->middleware('auth:sanctum')->group(function () {
    Route::apiResource('notifications', NotificationController::class)->only(['index','show','store']);
    Route::get('loyalty/points/{customer}', [LoyaltyController::class, 'points']);
    Route::get('loyalty/coupons/{customer}', [LoyaltyController::class, 'coupons']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('countries', CountryController::class)->only(['index','store']);
    Route::apiResource('cities', CityController::class)->only(['index','store']);
});

