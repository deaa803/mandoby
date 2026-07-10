<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $now = now();
        Storage::disk('public')->makeDirectory('seed/companies');
        Storage::disk('public')->makeDirectory('seed/products');
        Storage::disk('public')->makeDirectory('seed/ads');
        Storage::disk('public')->makeDirectory('seed/models');

        $adminUserId = DB::table('users')->insertGetId([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'latitude' => 33.5138050,
            'longitude' => 36.2765270,
            'phone' => '0990000000',
            'address' => 'دمشق - ساحة الأمويين',
            'user_type' => 'admin',
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $companiesData = [
            [
                'owner' => 'شركة الشام الغذائية',
                'email' => 'company1@example.com',
                'phone' => '0933111101',
                'address' => 'دمشق - المزة',
                'lat' => 33.5037,
                'lng' => 36.2486,
                'name' => 'الشام للمواد الغذائية',
                'description' => 'توريد مواد غذائية وتموينية للمتاجر والمطاعم.',
                'radius' => 8,
                'fee' => 1000,
                'logo_asset' => 'companies/company-1.jpg',
            ],
            [
                'owner' => 'شركة بردى للتنظيف',
                'email' => 'company2@example.com',
                'phone' => '0933111102',
                'address' => 'دمشق - كفرسوسة',
                'lat' => 33.4891,
                'lng' => 36.2718,
                'name' => 'بردى للمنظفات',
                'description' => 'منظفات منزلية وصناعية بالجملة.',
                'radius' => 10,
                'fee' => 1000,
                'logo_asset' => 'companies/company-2.jpg',
            ],
            [
                'owner' => 'شركة أوغاريت للتقنية',
                'email' => 'company3@example.com',
                'phone' => '0933111103',
                'address' => 'دمشق - البرامكة',
                'lat' => 33.5102,
                'lng' => 36.2939,
                'name' => 'أوغاريت للإلكترونيات',
                'description' => 'إكسسوارات ومستلزمات إلكترونية للمتاجر.',
                'radius' => 6,
                'fee' => 1250,
                'logo_asset' => 'companies/company-3.jpg',
            ],
            [
                'owner' => 'شركة الياسمين للعناية',
                'email' => 'company4@example.com',
                'phone' => '0933111104',
                'address' => 'دمشق - المالكي',
                'lat' => 33.5196,
                'lng' => 36.2734,
                'name' => 'الياسمين للعناية الشخصية',
                'description' => 'منتجات عناية شخصية وتجميل للمتاجر.',
                'radius' => 12,
                'fee' => 900,
                'logo_asset' => 'companies/company-4.jpg',
            ],
            [
                'owner' => 'شركة القلم للقرطاسية',
                'email' => 'company5@example.com',
                'phone' => '0933111105',
                'address' => 'دمشق - ركن الدين',
                'lat' => 33.5368,
                'lng' => 36.3090,
                'name' => 'القلم للقرطاسية',
                'description' => 'قرطاسية ومستلزمات مكاتب ومدارس.',
                'radius' => 7,
                'fee' => 1000,
                'logo_asset' => 'companies/company-5.jpg',
            ],
            [
                'owner' => 'شركة البيت الحديث',
                'email' => 'company6@example.com',
                'phone' => '0933111106',
                'address' => 'دمشق - جرمانا',
                'lat' => 33.4880,
                'lng' => 36.3488,
                'name' => 'البيت الحديث للأدوات المنزلية',
                'description' => 'أدوات منزلية ومستلزمات مطابخ بالجملة.',
                'radius' => 9,
                'fee' => 1100,
                'logo_asset' => 'companies/company-6.jpg',
            ],
        ];

        $companyIds = [];
        $companies = [];

        foreach ($companiesData as $index => $item) {
            $userId = DB::table('users')->insertGetId([
                'name' => $item['owner'],
                'email' => $item['email'],
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'latitude' => $item['lat'],
                'longitude' => $item['lng'],
                'phone' => $item['phone'],
                'address' => $item['address'],
                'user_type' => 'company',
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $logoPath = $this->copySeedAsset($item['logo_asset'], "seed/companies/company-" . ($index + 1) . '.jpg');

            $companyId = DB::table('companies')->insertGetId([
                'user_id' => $userId,
                'name_company' => $item['name'],
                'description' => $item['description'],
                'logo' => $logoPath,
                'delivery_radius_km' => $item['radius'],
                'extra_delivery_fee_per_km' => $item['fee'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $companyIds[] = $companyId;
            $companies[$companyId] = $item + ['id' => $companyId, 'user_id' => $userId];
        }

        $storesData = [
            ['name' => 'ماركت النور', 'email' => 'store1@example.com', 'phone' => '0944111101', 'address' => 'دمشق - أبو رمانة', 'lat' => 33.5191, 'lng' => 36.2845, 'activity' => 'مواد غذائية'],
            ['name' => 'سوبر ماركت البركة', 'email' => 'store2@example.com', 'phone' => '0944111102', 'address' => 'دمشق - مشروع دمر', 'lat' => 33.5434, 'lng' => 36.2265, 'activity' => 'مواد غذائية'],
            ['name' => 'متجر الشفاء', 'email' => 'store3@example.com', 'phone' => '0944111103', 'address' => 'دمشق - الميدان', 'lat' => 33.4865, 'lng' => 36.3041, 'activity' => 'عناية شخصية'],
            ['name' => 'ميني ماركت الروضة', 'email' => 'store4@example.com', 'phone' => '0944111104', 'address' => 'دمشق - الروضة', 'lat' => 33.5241, 'lng' => 36.2758, 'activity' => 'منظفات'],
            ['name' => 'قرطاسية النجاح', 'email' => 'store5@example.com', 'phone' => '0944111105', 'address' => 'دمشق - شارع بغداد', 'lat' => 33.5178, 'lng' => 36.3033, 'activity' => 'قرطاسية'],
            ['name' => 'بيت المونة', 'email' => 'store6@example.com', 'phone' => '0944111106', 'address' => 'ريف دمشق - قدسيا', 'lat' => 33.5488, 'lng' => 36.2151, 'activity' => 'مواد غذائية'],
            ['name' => 'ركن التقنية', 'email' => 'store7@example.com', 'phone' => '0944111107', 'address' => 'دمشق - الزاهرة', 'lat' => 33.4814, 'lng' => 36.3232, 'activity' => 'إلكترونيات'],
            ['name' => 'أدوات البيت', 'email' => 'store8@example.com', 'phone' => '0944111108', 'address' => 'دمشق - باب توما', 'lat' => 33.5128, 'lng' => 36.3157, 'activity' => 'أدوات منزلية'],
        ];

        $storeIds = [];
        $stores = [];

        foreach ($storesData as $item) {
            $userId = DB::table('users')->insertGetId([
                'name' => $item['name'] . ' - المالك',
                'email' => $item['email'],
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'latitude' => $item['lat'],
                'longitude' => $item['lng'],
                'phone' => $item['phone'],
                'address' => $item['address'],
                'user_type' => 'store',
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $storeId = DB::table('stores')->insertGetId([
                'user_id' => $userId,
                'name_store' => $item['name'],
                'activity_type' => $item['activity'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $storeIds[] = $storeId;
            $stores[$storeId] = $item + ['id' => $storeId, 'user_id' => $userId];
        }

        $this->seedSubscriptions($companyIds, $now);

        $cars = [];
        $driverIdsByCompany = [];

        foreach ($companyIds as $companyIndex => $companyId) {
            for ($i = 1; $i <= 2; $i++) {
                $sequence = ($companyIndex * 2) + $i;
                $carId = DB::table('company_cars')->insertGetId([
                    'company_id' => $companyId,
                    'vehicle_type' => $i === 1 ? 'Van' : 'Motorcycle',
                    'plate_number' => 'SY-' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $driverUserId = DB::table('users')->insertGetId([
                    'name' => 'سائق ' . $companies[$companyId]['name'] . ' ' . $i,
                    'email' => "driver{$sequence}@example.com",
                    'email_verified_at' => $now,
                    'password' => Hash::make('password'),
                    'latitude' => $companies[$companyId]['lat'],
                    'longitude' => $companies[$companyId]['lng'],
                    'phone' => '0955' . str_pad((string) $sequence, 6, '0', STR_PAD_LEFT),
                    'address' => $companies[$companyId]['address'],
                    'user_type' => 'driver',
                    'remember_token' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $driverId = DB::table('drivers')->insertGetId([
                    'user_id' => $driverUserId,
                    'company_car_id' => $carId,
                    'fcm_token' => null,
                    'status' => 'available',
                    'current_lat' => $companies[$companyId]['lat'],
                    'current_lng' => $companies[$companyId]['lng'],
                    'last_location_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $cars[] = ['id' => $carId, 'company_id' => $companyId, 'driver_id' => $driverId];
                $driverIdsByCompany[$companyId][] = $driverId;
            }
        }

        foreach ($companyIds as $companyId) {
            foreach ($storeIds as $storeId) {
                DB::table('company_store')->insert([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'return_days' => [0, 3, 7, 14][($companyId + $storeId) % 4],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $categoryMap = $this->seedCategories($now);
        $featureMap = $this->seedProductFeatures($now);
        [$productDetailsByCompany, $discountsByProductDetail] = $this->seedProducts(
            $companies,
            $categoryMap,
            $featureMap,
            $now,
        );

        $this->seedAdvertisements($productDetailsByCompany, $now);
        $this->seedOrders(
            $companies,
            $stores,
            $productDetailsByCompany,
            $discountsByProductDetail,
            $driverIdsByCompany,
            $now,
        );
    }

    private function seedSubscriptions(array $companyIds, $now): void
    {
        $features = [
            '3d_models' => ['name' => 'موديلات ثلاثية الأبعاد', 'description' => 'إنشاء وعرض موديلات 3D للمنتجات.'],
            'advanced_reports' => ['name' => 'التقارير المتقدمة', 'description' => 'تقارير مبيعات وأرباح وخصومات وحركة مخزون.'],
        ];

        $featureIds = [];

        foreach ($features as $key => $feature) {
            $featureIds[$key] = DB::table('subscription_features')->insertGetId([
                'name' => $feature['name'],
                'key' => $key,
                'description' => $feature['description'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $plans = [
            'reports' => ['name' => 'Reports', 'price' => 25000, 'duration_days' => 30, 'features' => ['advanced_reports'], 'description' => 'تقارير متقدمة للشركات.'],
            'pro' => ['name' => 'Pro', 'price' => 50000, 'duration_days' => 30, 'features' => ['advanced_reports', '3d_models'], 'description' => 'تقارير متقدمة + موديلات 3D.'],
            'annual' => ['name' => 'Annual Pro', 'price' => 450000, 'duration_days' => 365, 'features' => ['advanced_reports', '3d_models'], 'description' => 'اشتراك سنوي كامل.'],
        ];

        $planIds = [];

        foreach ($plans as $key => $plan) {
            $planIds[$key] = DB::table('subscription_plans')->insertGetId([
                'name' => $plan['name'],
                'price' => $plan['price'],
                'duration_days' => $plan['duration_days'],
                'description' => $plan['description'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($plan['features'] as $featureKey) {
                DB::table('subscription_plan_features')->insert([
                    'subscription_plan_id' => $planIds[$key],
                    'subscription_feature_id' => $featureIds[$featureKey],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        foreach ($companyIds as $index => $companyId) {
            $planKey = $index % 3 === 0 ? 'annual' : ($index % 2 === 0 ? 'pro' : 'reports');
            $endDate = $index === 1 ? $now->copy()->addDays(3) : ($index === 4 ? $now->copy()->subDay() : $now->copy()->addDays(45 + $index));
            $status = $endDate->isPast() ? 'expired' : ($endDate->diffInDays($now) <= 7 ? 'expiring' : 'active');

            DB::table('company_subscriptions')->insert([
                'company_id' => $companyId,
                'subscription_plan_id' => $planIds[$planKey],
                'start_date' => $now->copy()->subDays(20),
                'end_date' => $endDate,
                'status' => $status,
                'last_expiring_7_notified_at' => $status === 'expiring' ? $now->copy()->subDay() : null,
                'last_expiring_3_notified_at' => null,
                'expired_notified_at' => $status === 'expired' ? $now->copy()->subHours(5) : null,
                'notes' => $status === 'expired' ? 'اشتراك منتهي للتجربة' : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedCategories($now): array
    {
        $names = [
            'مواد غذائية' => 'مواد تموينية ومعلبات وزيوت.',
            'مشروبات' => 'مشروبات باردة وساخنة ومياه.',
            'منظفات' => 'منظفات منزلية وصناعية.',
            'إلكترونيات' => 'إكسسوارات ومستلزمات تقنية.',
            'قرطاسية' => 'دفاتر وأقلام ومستلزمات مكاتب.',
            'عناية شخصية' => 'شامبو وصابون وعناية يومية.',
            'أدوات منزلية' => 'مستلزمات مطابخ وتنظيم منزلي.',
        ];

        $map = [];

        foreach ($names as $name => $description) {
            $map[$name] = DB::table('categories')->insertGetId([
                'name' => $name,
                'description' => $description,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $map;
    }

    private function seedProductFeatures($now): array
    {
        $names = ['الوزن', 'الحجم', 'بلد المنشأ', 'نوع التغليف', 'العدد بالكرتونة', 'مدة الصلاحية', 'النكهة', 'السعة', 'المادة', 'الضمان'];
        $map = [];

        foreach ($names as $name) {
            $map[$name] = DB::table('features')->insertGetId([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return $map;
    }

    private function seedProducts(array $companies, array $categoryMap, array $featureMap, $now): array
    {
        $catalog = [
            ['name' => 'رز قصير الحبة 5 كغ', 'category' => 'مواد غذائية', 'price' => 48000, 'min' => 2, 'features' => ['الوزن' => '5 كغ', 'بلد المنشأ' => 'سوريا', 'نوع التغليف' => 'كيس'], 'discount' => [10, 8]],
            ['name' => 'زيت دوار الشمس 4 لتر', 'category' => 'مواد غذائية', 'price' => 62000, 'min' => 3, 'features' => ['السعة' => '4 لتر', 'بلد المنشأ' => 'محلي', 'نوع التغليف' => 'عبوة بلاستيك'], 'discount' => [12, 10]],
            ['name' => 'شاي أسود فاخر 800 غ', 'category' => 'مشروبات', 'price' => 35000, 'min' => 4, 'features' => ['الوزن' => '800 غ', 'نوع التغليف' => 'علبة', 'مدة الصلاحية' => '18 شهر'], 'discount' => [8, 7]],
            ['name' => 'مسحوق غسيل 3 كغ', 'category' => 'منظفات', 'price' => 30000, 'min' => 5, 'features' => ['الوزن' => '3 كغ', 'الرائحة' => 'لافندر', 'نوع التغليف' => 'كيس'], 'discount' => [15, 12]],
            ['name' => 'سائل جلي 1 لتر', 'category' => 'منظفات', 'price' => 12000, 'min' => 6, 'features' => ['السعة' => '1 لتر', 'الرائحة' => 'ليمون', 'نوع التغليف' => 'عبوة'], 'discount' => [20, 10]],
            ['name' => 'شاحن سريع Type-C', 'category' => 'إلكترونيات', 'price' => 45000, 'min' => 2, 'features' => ['الضمان' => '6 أشهر', 'بلد المنشأ' => 'الصين', 'نوع التغليف' => 'علبة'], 'discount' => [6, 6]],
            ['name' => 'سماعات سلكية', 'category' => 'إلكترونيات', 'price' => 28000, 'min' => 3, 'features' => ['الضمان' => '3 أشهر', 'اللون' => 'أسود', 'نوع التغليف' => 'علبة'], 'discount' => null],
            ['name' => 'دفتر جامعي 100 ورقة', 'category' => 'قرطاسية', 'price' => 7000, 'min' => 12, 'features' => ['العدد بالكرتونة' => '120 دفتر', 'المادة' => 'ورق أبيض', 'الحجم' => 'A4'], 'discount' => [50, 15]],
            ['name' => 'قلم حبر أزرق', 'category' => 'قرطاسية', 'price' => 2500, 'min' => 24, 'features' => ['العدد بالكرتونة' => '50 قلم', 'اللون' => 'أزرق', 'بلد المنشأ' => 'سوريا'], 'discount' => [100, 20]],
            ['name' => 'شامبو أعشاب 700 مل', 'category' => 'عناية شخصية', 'price' => 22000, 'min' => 6, 'features' => ['السعة' => '700 مل', 'الرائحة' => 'أعشاب', 'مدة الصلاحية' => '24 شهر'], 'discount' => [12, 9]],
            ['name' => 'صابون سائل 500 مل', 'category' => 'عناية شخصية', 'price' => 10500, 'min' => 8, 'features' => ['السعة' => '500 مل', 'الرائحة' => 'ورد', 'نوع التغليف' => 'عبوة'], 'discount' => [16, 8]],
            ['name' => 'علب حفظ طعام 5 قطع', 'category' => 'أدوات منزلية', 'price' => 32000, 'min' => 2, 'features' => ['المادة' => 'بلاستيك غذائي', 'العدد بالكرتونة' => '24 طقم', 'الحجم' => 'متعدد'], 'discount' => [10, 10]],
        ];

        $productDetailsByCompany = [];
        $discountsByProductDetail = [];
        $imageIndex = 1;

        foreach ($companies as $companyId => $company) {
            foreach ($catalog as $index => $item) {
                if ($index % count($companies) !== (($companyId - 1) % count($companies)) && $index % 2 !== ($companyId % 2)) {
                    continue;
                }

                $productId = DB::table('products')->insertGetId([
                    'name' => $item['name'],
                    'description' => 'منتج تجريبي منظم مخصص لتجربة الطلبات والعروض والتقارير.',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $price = $item['price'] + (($companyId % 3) * 1500);

                $productDetailId = DB::table('product_details')->insertGetId([
                    'product_id' => $productId,
                    'company_id' => $companyId,
                    'category_id' => $categoryMap[$item['category']],
                    'status' => 'available',
                    'price' => $price,
                    'min_order_quantity' => $item['min'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $productDetailsByCompany[$companyId][] = [
                    'id' => $productDetailId,
                    'price' => $price,
                    'name' => $item['name'],
                ];

                foreach ($item['features'] as $featureName => $value) {
                    if (! isset($featureMap[$featureName])) {
                        continue;
                    }

                    DB::table('feature_product_details')->insert([
                        'feature_id' => $featureMap[$featureName],
                        'product_detail_id' => $productDetailId,
                        'value' => $value,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                if ($item['discount']) {
                    [$quantity, $percentage] = $item['discount'];
                    DB::table('product_discounts')->insert([
                        'product_detail_id' => $productDetailId,
                        'quantity' => $quantity,
                        'discount_percentage' => $percentage,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    $discountsByProductDetail[$productDetailId] = [
                        'quantity' => $quantity,
                        'percentage' => $percentage,
                    ];
                }

                $mainImage = $this->copySeedAsset('products/product-' . $imageIndex . '.jpg', "seed/products/product-{$productDetailId}-1.jpg");
                $secondImage = $this->copySeedAsset('products/product-' . (($imageIndex % 24) + 1) . '.jpg', "seed/products/product-{$productDetailId}-2.jpg");

                foreach ([$mainImage, $secondImage] as $path) {
                    DB::table('images')->insert([
                        'product_detail_id' => $productDetailId,
                        'url' => $path,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                if ($imageIndex <= 4) {
                    $thumbnail = $this->copySeedAsset('models/3d-' . $imageIndex . '.jpg', "seed/models/model-{$productDetailId}.jpg");
                    DB::table('product_3d_models')->insert([
                        'product_detail_id' => $productDetailId,
                        'company_id' => $companyId,
                        'source_image' => $mainImage,
                        'model_file' => null,
                        'thumbnail' => $thumbnail,
                        'status' => 'completed',
                        'progress' => 100,
                        'error_message' => null,
                        'metadata' => json_encode(['seed' => true, 'note' => 'Dummy seed 3D record']),
                        'started_at' => $now->copy()->subHours(3),
                        'generated_at' => $now->copy()->subHours(2),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $imageIndex = $imageIndex >= 24 ? 1 : $imageIndex + 1;
            }
        }

        return [$productDetailsByCompany, $discountsByProductDetail];
    }

    private function seedAdvertisements(array $productDetailsByCompany, $now): void
    {
        $adIndex = 1;

        foreach ($productDetailsByCompany as $companyId => $items) {
            if ($adIndex > 6 || empty($items)) {
                break;
            }

            $product = $items[0];
            $image = $this->copySeedAsset('ads/offer-' . $adIndex . '.jpg', "seed/ads/ad-{$adIndex}.jpg");

            DB::table('advertisements')->insert([
                'company_id' => $companyId,
                'product_detail_id' => $product['id'],
                'title' => 'عرض خاص على ' . $product['name'],
                'description' => 'عرض ممول تجريبي يظهر ضمن واجهة العروض.',
                'image' => $image,
                'price' => 15000 + ($adIndex * 5000),
                'status' => $adIndex % 3 === 0 ? 'pending' : 'active',
                'starts_at' => $now->copy()->subDays(2),
                'ends_at' => $now->copy()->addDays(20),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $adIndex++;
        }
    }

    private function seedOrders(array $companies, array $stores, array $productDetailsByCompany, array $discountsByProductDetail, array $driverIdsByCompany, $now): void
    {
        $statuses = ['pending', 'preparing', 'delivering', 'delivered', 'cancelled'];
        $orderNumber = 1;

        foreach ($companies as $companyId => $company) {
            $companyProducts = $productDetailsByCompany[$companyId] ?? [];
            if (empty($companyProducts)) {
                continue;
            }

            foreach (array_slice($stores, 0, 5, true) as $storeId => $store) {
                $status = $statuses[$orderNumber % count($statuses)];
                $driverId = in_array($status, ['delivering', 'delivered'], true)
                    ? ($driverIdsByCompany[$companyId][0] ?? null)
                    : null;

                $selected = array_slice($companyProducts, 0, min(3, count($companyProducts)));
                $lines = [];
                $productsTotal = 0;

                foreach ($selected as $lineIndex => $product) {
                    $quantity = [3, 6, 12, 20][($orderNumber + $lineIndex) % 4];
                    $price = (float) $product['price'];
                    $gross = $price * $quantity;
                    $discountRule = $discountsByProductDetail[$product['id']] ?? null;
                    $discountPercent = $discountRule && $quantity >= $discountRule['quantity']
                        ? $discountRule['percentage']
                        : 0;
                    $discount = round($gross * ($discountPercent / 100), 2);
                    $productsTotal += $gross - $discount;

                    $lines[] = [
                        'product_detail_id' => $product['id'],
                        'quantity' => $quantity,
                        'price' => $price,
                        'discount' => $discount,
                    ];
                }

                $delivery = $this->deliveryFeeFor($store, $company);
                $total = round($productsTotal + $delivery['extra_delivery_fee'], 2);
                $paidAmount = $status === 'delivered'
                    ? $total
                    : ($status === 'cancelled' ? 0 : round($total * 0.35, 2));
                $remainingAmount = max(0, $total - $paidAmount);
                $qrCode = 'ORD-SEED-' . str_pad((string) $orderNumber, 4, '0', STR_PAD_LEFT) . '-' . Str::upper(Str::random(6));
                $createdAt = $now->copy()->subDays($orderNumber);

                $orderId = DB::table('orders')->insertGetId([
                    'store_id' => $storeId,
                    'driver_id' => $driverId,
                    'total_price' => $total,
                    'date' => $createdAt->toDateString(),
                    'commission' => round($total * 0.02, 2),
                    'status' => $status,
                    'estimated_delivery_minutes' => $status === 'cancelled' ? null : 60 + ($orderNumber % 5) * 15,
                    'estimated_delivery_at' => $status === 'cancelled' ? null : $createdAt->copy()->addHours(3),
                    'eta_last_calculated_at' => $createdAt,
                    'delivery_distance_km' => $delivery['delivery_distance_km'],
                    'extra_delivery_km' => $delivery['extra_delivery_km'],
                    'extra_delivery_fee' => $delivery['extra_delivery_fee'],
                    'delivery_qr_code' => $qrCode,
                    'delivery_qr_used_at' => $status === 'delivered' ? $createdAt->copy()->addHours(4) : null,
                    'delivered_at' => $status === 'delivered' ? $createdAt->copy()->addHours(4) : null,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                    'created_at' => $createdAt,
                    'updated_at' => $now,
                ]);

                foreach ($lines as $line) {
                    DB::table('order_product_detail')->insert([
                        'order_id' => $orderId,
                        'product_detail_id' => $line['product_detail_id'],
                        'discount' => $line['discount'],
                        'quantity' => $line['quantity'],
                        'price' => $line['price'],
                        'created_at' => $createdAt,
                        'updated_at' => $now,
                    ]);
                }

                if ($paidAmount > 0) {
                    DB::table('payments')->insert([
                        'order_id' => $orderId,
                        'amount' => $paidAmount,
                        'paid_at' => $createdAt->copy()->addHour(),
                        'note' => $status === 'delivered' ? 'تم الدفع كاملاً' : 'دفعة أولى تجريبية',
                        'created_at' => $createdAt,
                        'updated_at' => $now,
                    ]);
                }

                $orderNumber++;
            }
        }
    }

    private function deliveryFeeFor(array $store, array $company): array
    {
        $distance = round($this->distanceKm($company['lat'], $company['lng'], $store['lat'], $store['lng']), 2);
        $extraKm = (int) ceil(max(0, $distance - (float) $company['radius']));
        $extraFee = round($extraKm * (float) $company['fee'], 2);

        return [
            'delivery_distance_km' => $distance,
            'extra_delivery_km' => $extraKm,
            'extra_delivery_fee' => $extraFee,
        ];
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    private function copySeedAsset(string $asset, string $target): string
    {
        $source = database_path('seeders/assets/' . $asset);

        if (is_file($source)) {
            Storage::disk('public')->put($target, file_get_contents($source));
        }

        return $target;
    }
}
