<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\Api\DeviceTokenController;
use App\Http\Controllers\Api\FirebaseTestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyCarController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyReportController;
use App\Http\Controllers\CompanySubscriptionController;
use App\Http\Controllers\CompanyDashboardController;
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
use App\Http\Controllers\TestPushController;
use App\Http\Middleware\CheckUserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication and registration
|--------------------------------------------------------------------------
*/
Route::post('order/store', [OrderController::class, 'store']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/driver/login', [AuthController::class, 'logindriver']);

Route::post('/register', [StoreController::class, 'store']);
Route::post('/register/store', [StoreController::class, 'store']);
Route::post('/register/company', [CompanyController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Test push
|--------------------------------------------------------------------------
|
| أبقيناه متاحاً مؤقتاً لتجربة الإشعارات كما طلبت.
| يفضّل حمايته أو حذفه قبل نشر النسخة الإنتاجية.
|
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    Route::post('/firebase/test-notification', [FirebaseTestController::class, 'send']);

    Route::post('/tracking/orders/{order}/location', [TrackingController::class, 'updateOrderLocation']);
    Route::delete('/tracking/orders/{order}', [TrackingController::class, 'stopOrderTracking']);



});
/*
|--------------------------------------------------------------------------
| Public application data
|--------------------------------------------------------------------------
*/

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

Route::options('/product-3d-models/{product3DModel}/file', function () {
    return response('', 204)->withHeaders([
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
        'Access-Control-Allow-Headers' => 'Origin, Content-Type, Accept, Range',
        'Access-Control-Expose-Headers' =>
            'Content-Length, Content-Range, Accept-Ranges',
    ]);
});

Route::get(
    '/product-3d-models/{product3DModel}/file',
    [Product3DModelController::class, 'modelFile'],
)->name('product-3d-models.file');

Route::apiResource('/categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('/features', FeatureController::class)->only(['index', 'show']);
Route::apiResource('/products', ProductController::class)->only(['index', 'show']);
Route::apiResource('/advertisements', AdvertisementController::class)->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| Authenticated common routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load([
            'company.currentSubscription.plan.features',
            'store',
            'driver.car.company',
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});

/*
|--------------------------------------------------------------------------
| Company routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    CheckUserType::class . ':company',
])->group(function () {
    Route::get('/company/dashboard', [CompanyDashboardController::class, 'index']);
    Route::get('/company/subscription', [CompanySubscriptionController::class, 'current']);
    Route::get('/company/reports', [CompanyReportController::class, 'index']);
    Route::post('/company/profile/logo', [CompanyController::class, 'updateOwnLogo']);
    Route::post('/company/fcm-token', [DeviceTokenController::class, 'storeCompany']);

    Route::get('/company/products', [ProductDetailController::class, 'myCompanyProducts']);
    Route::get('/company/my-products', [ProductDetailController::class, 'myCompanyProducts']);

    Route::post('/product-details', [ProductDetailController::class, 'store']);
    Route::put('/product-details/{productDetail}', [ProductDetailController::class, 'update']);
    Route::patch('/product-details/{productDetail}', [ProductDetailController::class, 'update']);
    Route::delete('/product-details/{productDetail}', [ProductDetailController::class, 'destroy']);
    Route::post('/product-details/{productDetailId}/images', [ImageController::class, 'storeMultiple']);

    Route::get('/company/cars', [CompanyCarController::class, 'index']);
    Route::post('/company/cars', [CompanyCarController::class, 'store']);
    Route::get('/company/cars/{companyCar}', [CompanyCarController::class, 'show']);
    Route::put('/company/cars/{companyCar}', [CompanyCarController::class, 'update']);
    Route::patch('/company/cars/{companyCar}', [CompanyCarController::class, 'update']);
    Route::delete('/company/cars/{companyCar}', [CompanyCarController::class, 'destroy']);

    Route::get('/company/orders', [OrderController::class, 'companyOrders']);
    Route::get('/company/orders/{order}', [OrderController::class, 'showCompanyOrder']);
    Route::get('/company/orders/{order}/eta', [OrderController::class, 'estimateDelivery']);
    Route::get('/company/receivables', [OrderController::class, 'companyReceivables']);
    Route::get('/company/payments', [PaymentController::class, 'companyPayments']);
    Route::post('/company/payments', [PaymentController::class, 'store']);

    Route::post(
        '/company/orders/{order}/assign-driver',
        [OrderController::class, 'assignDriver'],
    );

    Route::post(
        '/company/orders/{order}/payments',
        [PaymentController::class, 'companyStorePayment'],
    );

    Route::get('/company/3d-models', [Product3DModelController::class, 'index']);
    Route::post('/company/3d-models', [Product3DModelController::class, 'store']);
    Route::get('/company/3d-models/{model3d}', [Product3DModelController::class, 'show']);
    Route::delete('/company/3d-models/{model3d}', [Product3DModelController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Store routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    CheckUserType::class . ':store',
])->group(function () {
    Route::get('/store/companies', [CompanyController::class, 'browse']);
    Route::post('/store/fcm-token', [DeviceTokenController::class, 'storeStore']);

    Route::post('/store/orders', [OrderController::class, 'store']);

    // يستقبل سلة من عدة شركات وينشئ طلباً مستقلاً لكل شركة.
    Route::post('/store/orders/batch', [OrderController::class, 'storeBatch']);

    Route::get('/store/my-orders', [OrderController::class, 'myOrders']);
    Route::get('/store/orders/current', [OrderController::class, 'myCurrentOrders']);
    Route::get('/store/orders/completed', [OrderController::class, 'myCompletedOrders']);
    Route::get('/store/orders/{order}', [OrderController::class, 'showStoreOrder']);
    Route::get('/store/orders/{order}/eta', [OrderController::class, 'estimateDelivery']);
    Route::get('/store/my-debts', [OrderController::class, 'myDebts']);

    Route::get('/store/payments', [PaymentController::class, 'storePayments']);
});
/*
|--------------------------------------------------------------------------
| Driver routes
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth:sanctum',
    CheckUserType::class . ':driver',
])->group(function () {
    Route::get('/driver/profile', [DriverAppController::class, 'profile']);

    Route::post('/driver/fcm-token', [
        DriverAppController::class,
        'saveFcmToken',
    ]);

    Route::get('/driver/current-order', [
        DriverAppController::class,
        'currentOrder',
    ]);

    Route::get('/driver/delivery-history', [
        DriverAppController::class,
        'deliveryHistory',
    ]);

    /*
    |--------------------------------------------------------------------------
    | Delivery confirmation using QR Code
    |--------------------------------------------------------------------------
    */
    Route::post(
        '/driver/orders/{order}/confirm-delivery',
        [OrderController::class, 'confirmDelivery']
    );

    /*
    |--------------------------------------------------------------------------
    | Legacy endpoint
    |--------------------------------------------------------------------------

    */
    Route::post(
        '/driver/orders/{order}/delivered',
        [DriverAppController::class, 'markAsDelivered']
    );

    Route::post('/driver/location', [
        DriverController::class,
        'updateLocation',
    ]);

    Route::post('/drivers/fcm-token', [
        DriverFcmTokenController::class,
        'store',
    ]);
});
/*
|--------------------------------------------------------------------------
| Admin API routes
|--------------------------------------------------------------------------
|
| إنشاء العروض يتم حصراً من لوحة Filament. لا يوجد POST أو PUT للإعلانات
| ضمن API، كما أن تعديل العرض معطل في مورد Filament.
|
*/

Route::middleware([
    'auth:sanctum',
    CheckUserType::class . ':admin',
])->group(function () {
    Route::apiResource('/companies', CompanyController::class);
    Route::apiResource('/stores', StoreController::class);

    Route::apiResource('/products', ProductController::class)
        ->except(['index', 'show']);

    Route::apiResource('/categories', CategoryController::class)
        ->except(['index', 'show']);

    Route::apiResource('/features', FeatureController::class)
        ->except(['index', 'show']);

    Route::apiResource('/images', ImageController::class);
    Route::apiResource('/orders', OrderController::class);
    Route::apiResource('/payments', PaymentController::class);
    Route::apiResource('/drivers', DriverController::class);
    Route::post('/test-push', [TestPushController::class, 'send']);
});
