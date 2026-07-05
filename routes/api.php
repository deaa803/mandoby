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
use App\Http\Controllers\TestPushController;
use App\Http\Middleware\CheckUserType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

/**
 * تسجيل دخول الشركة أو المتجر.
 */
Route::post('/login', [AuthController::class, 'login']);

/**
 * تسجيل دخول السائق.
 */
Route::post('/driver/login', [AuthController::class, 'logindriver']);

/**
 * إرسال إشعار تجريبي بواسطة Firebase Cloud Messaging.
 */
Route::post('/test-push', [TestPushController::class, 'send']);


/*
|--------------------------------------------------------------------------
| Registration Routes
|--------------------------------------------------------------------------
*/

/**
 * تسجيل حساب متجر جديد.
 */
Route::post('/register', [StoreController::class, 'store']);

/**
 * تسجيل حساب متجر جديد.
 */
Route::post('/register/store', [StoreController::class, 'store']);

/**
 * تسجيل حساب شركة جديدة.
 */
Route::post('/register/company', [CompanyController::class, 'store']);


/*
|--------------------------------------------------------------------------
| Public Home Routes
|--------------------------------------------------------------------------
*/

/**
 * جلب بيانات الصفحة الرئيسية.
 */
Route::get('/home', [HomeController::class, 'homepage']);

/**
 * جلب العروض الفعالة والمتاحة حالياً.
 */
Route::get('/offers', [HomeController::class, 'offers']);

/**
 * جلب التصنيفات لعرضها في الصفحة الرئيسية.
 */
Route::get('/home/categories', [HomeController::class, 'categories']);

/**
 * جلب المنتجات المميزة.
 */
Route::get('/home/special-products', [HomeController::class, 'specialProducts']);

/**
 * جلب المنتجات حسب التصنيف المحدد.
 */
Route::get('/home/category/products/{id}', [
    HomeController::class,
    'productsByCategory',
]);

/**
 * جلب المنتجات التابعة لشركة محددة.
 */
Route::get('/home/company/products/{id}', [
    HomeController::class,
    'productsByCompany',
]);


/*
|--------------------------------------------------------------------------
| Public Product Details Routes
|--------------------------------------------------------------------------
*/

/**
 * جلب تفاصيل منتج محدد.
 */
Route::get('/product/details/{id}', [
    ProductDetailController::class,
    'show',
]);

/**
 * جلب تفاصيل منتج محدد.
 *
 * هذا المسار بديل للمسار السابق ومتوافق مع اسم Product Details.
 */
Route::get('/product-details/{id}', [
    ProductDetailController::class,
    'show',
]);


/*
|--------------------------------------------------------------------------
| Public 3D Model Routes
|--------------------------------------------------------------------------
*/

/**
 * جلب الموديل ثلاثي الأبعاد الخاص بمنتج محدد.
 */
Route::get('/products/{productDetail}/3d-model', [
    Product3DModelController::class,
    'forProduct',
]);

/**
 * جلب الموديل ثلاثي الأبعاد الخاص بتفاصيل منتج محدد.
 */
Route::get('/product-details/{productDetail}/3d-model', [
    Product3DModelController::class,
    'forProduct',
]);


/*
|--------------------------------------------------------------------------
| Public Company Cars Routes
|--------------------------------------------------------------------------
*/

/**
 * جلب جميع السيارات التابعة لشركة محددة.
 */
Route::get('/companies/{companyId}/cars', [
    CompanyCarController::class,
    'carsByCompany',
]);


/*
|--------------------------------------------------------------------------
| Public Resource Routes
|--------------------------------------------------------------------------
*/

/**
 * عرض جميع التصنيفات أو تصنيف محدد.
 */
Route::apiResource('/categories', CategoryController::class)
    ->only(['index', 'show']);

/**
 * عرض جميع الميزات أو ميزة محددة.
 */
Route::apiResource('/features', FeatureController::class)
    ->only(['index', 'show']);

/**
 * عرض جميع المنتجات أو منتج محدد.
 */
Route::apiResource('/products', ProductController::class)
    ->only(['index', 'show']);

/**
 * عرض جميع الإعلانات أو إعلان محدد.
 */
Route::apiResource('/advertisements', AdvertisementController::class)
    ->only(['index', 'show']);


/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
|
| جميع المسارات الموجودة داخل هذه المجموعة تحتاج إلى Sanctum Token.
|
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authenticated User Routes
    |--------------------------------------------------------------------------
    */

    /**
     * جلب بيانات المستخدم المسجل حالياً مع بيانات الشركة أو المتجر أو السائق.
     */
    Route::get('/user', function (Request $request) {
        return $request->user()->load([
            'company',
            'store',
            'driver.company',
            'driver.car',
        ]);
    });

    /**
     * تسجيل خروج المستخدم وحذف التوكن الحالي.
     */
    Route::post('/logout', [AuthController::class, 'logout']);


    /*
    |--------------------------------------------------------------------------
    | Main Resource Routes
    |--------------------------------------------------------------------------
    */

    /**
     * إدارة الشركات:
     * عرض، إضافة، تعديل وحذف الشركات.
     */
    Route::apiResource('/companies', CompanyController::class);

    /**
     * إدارة المتاجر:
     * عرض، إضافة، تعديل وحذف المتاجر.
     */
    Route::apiResource('/stores', StoreController::class);

    /**
     * إدارة المنتجات.
     *
     * تم استثناء index و show لأنهما متاحان بدون تسجيل دخول.
     */
    Route::apiResource('/products', ProductController::class)
        ->except(['index', 'show']);

    /**
     * إدارة تفاصيل المنتجات.
     *
     * تم استثناء show لأنه متاح بدون تسجيل دخول.
     */
    Route::apiResource('/product-details', ProductDetailController::class)
        ->except(['show']);

    /**
     * إدارة التصنيفات.
     *
     * تم استثناء index و show لأنهما متاحان بدون تسجيل دخول.
     */
    Route::apiResource('/categories', CategoryController::class)
        ->except(['index', 'show']);

    /**
     * إدارة الميزات.
     *
     * تم استثناء index و show لأنهما متاحان بدون تسجيل دخول.
     */
    Route::apiResource('/features', FeatureController::class)
        ->except(['index', 'show']);

    /**
     * إدارة صور المنتجات.
     */
    Route::apiResource('/images', ImageController::class);

    /**
     * رفع عدة صور لتفاصيل منتج محدد.
     */
    Route::post('/product-details/{productDetailId}/images', [
        ImageController::class,
        'storeMultiple',
    ]);

    /**
     * إدارة الإعلانات والعروض.
     *
     * تم استثناء index و show لأنهما متاحان بدون تسجيل دخول.
     */
    Route::apiResource('/advertisements', AdvertisementController::class)
        ->except(['index', 'show']);


    /*
    |--------------------------------------------------------------------------
    | Company Product Routes
    |--------------------------------------------------------------------------
    */

    /**
     * جلب المنتجات التابعة للشركة المسجلة حالياً.
     */
    Route::get('/company/my-products', [
        ProductDetailController::class,
        'myCompanyProducts',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Company 3D Models Routes
    |--------------------------------------------------------------------------
    |
    | هذه المسارات مخصصة للمستخدم المسجل من نوع company فقط.
    | إنشاء النتيجة وتحديث الحالة يتمان تلقائياً من خلال Queue Job.
    |
    */

    Route::middleware(CheckUserType::class . ':company')->group(function () {

        /**
         * جلب جميع عمليات وموديلات 3D التابعة للشركة الحالية.
         */
        Route::get('/company/3d-models', [
            Product3DModelController::class,
            'index',
        ]);

        /**
         * رفع صورة منتج وبدء إنشاء موديل 3D في الخلفية.
         */
        Route::post('/company/3d-models', [
            Product3DModelController::class,
            'store',
        ]);

        /**
         * جلب تفاصيل العملية وحالة إنشاء موديل 3D محدد.
         */
        Route::get('/company/3d-models/{model3d}', [
            Product3DModelController::class,
            'show',
        ]);

        /**
         * حذف عملية 3D وملفاتها التابعة للشركة الحالية.
         */
        Route::delete('/company/3d-models/{model3d}', [
            Product3DModelController::class,
            'destroy',
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | Company Cars Routes
    |--------------------------------------------------------------------------
    */

    /**
     * جلب سيارات الشركة المسجلة حالياً.
     */
    Route::get('/company/cars', [
        CompanyCarController::class,
        'index',
    ]);

    /**
     * إضافة سيارة جديدة للشركة وإنشاء بيانات السائق المرتبط بها.
     */
    Route::post('/company/cars', [
        CompanyCarController::class,
        'store',
    ]);

    /**
     * تعديل بيانات سيارة تابعة للشركة.
     */
    Route::put('/company/cars/{companyCar}', [
        CompanyCarController::class,
        'update',
    ]);

    /**
     * حذف سيارة تابعة للشركة.
     */
    Route::delete('/company/cars/{companyCar}', [
        CompanyCarController::class,
        'destroy',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Company Orders and Payments Routes
    |--------------------------------------------------------------------------
    */

    /**
     * جلب الطلبات المرتبطة بالشركة المسجلة حالياً.
     */
    Route::get('/company/orders', [
        OrderController::class,
        'companyOrders',
    ]);

    /**
     * جلب الدفعات المرتبطة بطلبات الشركة.
     */
    Route::get('/company/payments', [
        PaymentController::class,
        'companyPayments',
    ]);

    Route::get('/company/installments', [
        PaymentController::class,
        'companyInstallments',
    ]);

    /**
     * تعيين سائق لتوصيل طلب محدد.
     */
    Route::post('/orders/{order}/assign-driver', [
        OrderController::class,
        'assignDriver',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Store Orders Routes
    |--------------------------------------------------------------------------
    */

    /**
     * إنشاء طلب جديد من المتجر المسجل حالياً.
     */
    Route::post('/store/orders', [
        OrderController::class,
        'store',
    ]);

    /**
     * جلب جميع طلبات المتجر المسجل حالياً.
     */
    Route::get('/store/my-orders', [
        OrderController::class,
        'myOrders',
    ]);

    /**
     * جلب الطلبات الحالية وغير المكتملة للمتجر.
     */
    Route::get('/store/orders/current', [
        OrderController::class,
        'myCurrentOrders',
    ]);

    /**
     * جلب الطلبات المكتملة للمتجر.
     */
    Route::get('/store/orders/completed', [
        OrderController::class,
        'myCompletedOrders',
    ]);

    /**
     * جلب الديون والمبالغ المتبقية على المتجر.
     */
    Route::get('/store/my-debts', [
        OrderController::class,
        'myDebts',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Store Payments and Installments Routes
    |--------------------------------------------------------------------------
    */

    /**
     * جلب دفعات المتجر المسجل حالياً.
     */
    Route::get('/store/payments', [
        PaymentController::class,
        'storePayments',
    ]);

    /**
     * جلب أقساط المتجر والمبالغ المدفوعة والمتبقية.
     */
    Route::get('/store/installments', [
        PaymentController::class,
        'storeInstallments',
    ]);


    /*
    |--------------------------------------------------------------------------
    | Orders, Payments and Drivers Resource Routes
    |--------------------------------------------------------------------------
    */

    /**
     * إدارة الطلبات.
     *
     * تم استثناء store لأن إنشاء طلب المتجر له مسار مخصص.
     */
    Route::apiResource('/orders', OrderController::class)
        ->except(['store']);

    /**
     * إدارة الدفعات.
     */
    Route::apiResource('/payments', PaymentController::class);

    /**
     * إدارة السائقين.
     */
    Route::apiResource('/drivers', DriverController::class);


    /*
    |--------------------------------------------------------------------------
    | Driver Application Routes
    |--------------------------------------------------------------------------
    */

    /**
     * جلب الملف الشخصي للسائق المسجل حالياً.
     */
    Route::get('/driver/profile', [
        DriverAppController::class,
        'profile',
    ]);

    /**
     * حفظ أو تحديث Firebase FCM Token الخاص بجهاز السائق.
     */
    Route::post('/driver/fcm-token', [
        DriverAppController::class,
        'saveFcmToken',
    ]);

    /**
     * جلب الطلب الحالي المعيّن للسائق.
     */
    Route::get('/driver/current-order', [
        DriverAppController::class,
        'currentOrder',
    ]);

    /**
     * جلب سجل الطلبات التي قام السائق بتوصيلها.
     */
    Route::get('/driver/delivery-history', [
        DriverAppController::class,
        'deliveryHistory',
    ]);

    /**
     * تغيير حالة الطلب إلى تم التوصيل بواسطة السائق.
     */
    Route::post('/driver/orders/{order}/delivered', [
        DriverAppController::class,
        'markAsDelivered',
    ]);

    /**
     * تحديث موقع السائق الحالي باستخدام خط الطول وخط العرض.
     */
    Route::post('/driver/location', [
        DriverController::class,
        'updateLocation',
    ]);

    /**
     * حفظ Firebase FCM Token لسائق محدد.
     *
     * هذا المسار يستخدم DriverFcmTokenController.
     */
    Route::post('/drivers/fcm-token', [
        DriverFcmTokenController::class,
        'store',
    ]);
});
