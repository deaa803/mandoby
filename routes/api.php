<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\CompanyCarController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CompanyDashboardController;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [StoreController::class, 'store']);
Route::post('/register/store', [StoreController::class, 'store']);
Route::post('/register/company', [CompanyController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Public Home Routes
|--------------------------------------------------------------------------
*/

Route::get('home/category', [HomeController::class, 'categories']);

Route::get('home/product/special', [HomeController::class, 'specialProducts']);
Route::get('home/product/specail', [HomeController::class, 'specialProducts']);

Route::get('home/offer', [HomeController::class, 'offers']);

Route::get('home/category/products/{category}', [HomeController::class, 'productsByCategory']);
Route::get('home/company/products/{company}', [HomeController::class, 'productsByCompany']);

Route::get('product/details/{id}', [ProductDetailController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Public Basic Data
|--------------------------------------------------------------------------
*/

Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('features', FeatureController::class)->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
| Any route inside this group needs:
| Authorization: Bearer TOKEN
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Current User
    |--------------------------------------------------------------------------
    */

    Route::get('/user', function (Request $request) {
        return response()->json([
            'status' => true,
            'message' => 'User retrieved successfully',
            'data' => $request->user(),
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | General Protected CRUD
    |--------------------------------------------------------------------------
    */

    Route::apiResource('products', ProductController::class);
    Route::apiResource('product-details', ProductDetailController::class);
    Route::apiResource('images', ImageController::class);

    Route::post('product-details/{productDetail}/images', [ImageController::class, 'storeMultiple']);

    Route::apiResource('ad', AdvertisementController::class);

    /*
    |--------------------------------------------------------------------------
    | Store Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('user.type:store')->prefix('store')->group(function () {
        Route::get('my-orders', [OrderController::class, 'myOrders']);
        Route::get('my-debts', [OrderController::class, 'myDebts']);

        Route::apiResource('orders', OrderController::class);
        Route::apiResource('payments', PaymentController::class)->only(['index', 'show']);
        Route::apiResource('companies', CompanyController::class)->only(['index', 'show']);
    });

    /*
    |--------------------------------------------------------------------------
    | Company Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('user.type:company')->prefix('company')->group(function () {
        Route::get('orders', [OrderController::class, 'companyOrders']);
        Route::get('my-products', [ProductDetailController::class, 'myCompanyProducts']);
        Route::get('dashboard', [CompanyDashboardController::class, 'index']);

        Route::apiResource('cars', CompanyCarController::class);
        Route::apiResource('payments', PaymentController::class);
    });
});
