<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\CompanyCarController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CompanyController;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [\App\Http\Controllers\StoreController::class, 'store']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();


    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('ad',AdvertisementController::class);

    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::apiResource('features', FeatureController::class)->only(['index', 'show']);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('product-details', ProductDetailController::class);
    Route::apiResource('images', ImageController::class);

    Route::middleware('user.type:company')->prefix('company')->group(function () {
        Route::apiResource('cars', CompanyCarController::class);
        Route::apiResource('payments', PaymentController::class);
    });

    Route::middleware('user.type:store')->prefix('store')->group(function () {
        Route::apiResource('orders', OrderController::class);
        Route::apiResource('payments', PaymentController::class)->only(['index', 'show']);
        Route::apiResource('companies', CompanyController::class)->only(['index', 'show']);
    });
});

Route::get('home/category', [HomeController::class, 'categories']);
Route::get('home/product/specail', [HomeController::class, 'specialProducts']);
Route::get('home/offer', [HomeController::class, 'offers']);
Route::get('home/category/{category}/products', [HomeController::class, 'productsByCategory']);
Route::get('home/company/{company}/products', [HomeController::class, 'productsByCompany']);
