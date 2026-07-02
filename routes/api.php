<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyCarController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DriverAppController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\DriverFcmTokenController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\Product3DModelController;
use App\Http\Controllers\StoreController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/driver/login', [AuthController::class, 'logindriver']);

Route::post('/register', [StoreController::class, 'store']);
Route::post('/register/store', [StoreController::class, 'store']);
Route::post('/register/company', [CompanyController::class, 'store']);

Route::get('/home', [HomeController::class, 'homepage']);
Route::get('/offers', [HomeController::class, 'offers']);
Route::get('/home/categories', [HomeController::class, 'categories']);
Route::get('/home/special-products', [HomeController::class, 'specialProducts']);
Route::get('/home/category/products/{id}', [HomeController::class, 'productsByCategory']);
Route::get('/home/company/products/{id}', [HomeController::class, 'productsByCompany']);
Route::get('/product/details/{id}', [ProductDetailController::class, 'show']);
Route::get('/product-details/{id}', [ProductDetailController::class, 'show']);
Route::get('/products/{productDetail}/3d-model', [Product3DModelController::class, 'forProduct']);
Route::get('/product-details/{productDetail}/3d-model', [Product3DModelController::class, 'forProduct']);

Route::get('/companies/{companyId}/cars', [
    CompanyCarController::class,
    'carsByCompany'
]);
Route::apiResource('/categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('/features', FeatureController::class)->only(['index', 'show']);
Route::apiResource('/products', ProductController::class)->only(['index', 'show']);
Route::apiResource('/advertisements', AdvertisementController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user()->load(['company', 'store', 'driver.company', 'driver.car']));
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('/companies', CompanyController::class);
    Route::apiResource('/stores', StoreController::class);
    Route::apiResource('/products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('/product-details', ProductDetailController::class)->except(['show']);
    Route::apiResource('/categories', CategoryController::class)->except(['index', 'show']);
    Route::apiResource('/features', FeatureController::class)->except(['index', 'show']);
    Route::apiResource('/images', ImageController::class);
    Route::post('/product-details/{productDetailId}/images', [ImageController::class, 'storeMultiple']);
    Route::apiResource('/advertisements', AdvertisementController::class)->except(['index', 'show']);

    Route::get('/company/my-products', [ProductDetailController::class, 'myCompanyProducts']);
    Route::get('/company/3d-models', [Product3DModelController::class, 'index']);
    Route::post('/company/3d-models', [Product3DModelController::class, 'store']);
    Route::get('/company/3d-models/{model3d}', [Product3DModelController::class, 'show']);
    Route::patch('/company/3d-models/{model3d}', [Product3DModelController::class, 'update']);
    Route::post('/company/3d-models/{model3d}/result', [Product3DModelController::class, 'update']);
    Route::delete('/company/3d-models/{model3d}', [Product3DModelController::class, 'destroy']);
    Route::get('/company/cars', [CompanyCarController::class, 'index']);
    Route::post('/company/cars', [CompanyCarController::class, 'store']);
    Route::put('/company/cars/{companyCar}', [CompanyCarController::class, 'update']);
    Route::delete('/company/cars/{companyCar}', [CompanyCarController::class, 'destroy']);
    Route::get('/company/orders', [OrderController::class, 'companyOrders']);
    Route::get('/company/payments', [PaymentController::class, 'companyPayments']);
    Route::post('/orders/{order}/assign-driver', [OrderController::class, 'assignDriver']);

    Route::post('/store/orders', [OrderController::class, 'store']);
    Route::get('/store/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/store/orders/current', [OrderController::class, 'myCurrentOrders']);
    Route::get('/store/orders/completed', [OrderController::class, 'myCompletedOrders']);
    Route::get('/store/my-debts', [OrderController::class, 'myDebts']);
    Route::get('/store/payments', [PaymentController::class, 'storePayments']);
    Route::get('/store/installments', [PaymentController::class, 'storeInstallments']);

    Route::apiResource('/orders', OrderController::class)->except(['store']);
    Route::apiResource('/payments', PaymentController::class);
    Route::apiResource('/drivers', DriverController::class);

    Route::get('/driver/profile', [DriverAppController::class, 'profile']);
    Route::post('/driver/fcm-token', [DriverAppController::class, 'saveFcmToken']);
    Route::get('/driver/current-order', [DriverAppController::class, 'currentOrder']);
    Route::get('/driver/delivery-history', [DriverAppController::class, 'deliveryHistory']);
    Route::post('/driver/orders/{order}/delivered', [DriverAppController::class, 'markAsDelivered']);
    Route::post('/driver/location', [DriverController::class, 'updateLocation']);
    Route::post('/drivers/fcm-token', [DriverFcmTokenController::class, 'store']);
});
