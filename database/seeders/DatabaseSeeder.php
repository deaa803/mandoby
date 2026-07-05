<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */

        DB::table('users')->insert([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => $now,
            'password' => Hash::make('password'),
            'latitude' => 33.5138050,
            'longitude' => 36.2765270,
            'phone' => '0990000000',
            'user_type' => 'admin',
            'remember_token' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Stores
        |--------------------------------------------------------------------------
        */

        $storeIds = [];

        for ($i = 1; $i <= 20; $i++) {
            $userId = DB::table('users')->insertGetId([
                'name' => "Store User {$i}",
                'email' => "store{$i}@example.com",
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'latitude' => fake()->randomFloat(7, 33.4000000, 36.4000000),
                'longitude' => fake()->randomFloat(7, 35.5000000, 38.5000000),
                'phone' => '09' . rand(30000000, 99999999),
                'user_type' => 'store',
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $storeIds[] = DB::table('stores')->insertGetId([
                'user_id' => $userId,
                'name_store' => "Store {$i}",
                'activity_type' => fake()->randomElement([
                    'مواد غذائية',
                    'منظفات',
                    'ألبسة',
                    'إلكترونيات',
                    'مستلزمات منزلية',
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Companies
        |--------------------------------------------------------------------------
        */

        $companyIds = [];

        for ($i = 1; $i <= 10; $i++) {
            $userId = DB::table('users')->insertGetId([
                'name' => "Company User {$i}",
                'email' => "company{$i}@example.com",
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'latitude' => fake()->randomFloat(7, 33.4000000, 36.4000000),
                'longitude' => fake()->randomFloat(7, 35.5000000, 38.5000000),
                'phone' => '09' . rand(30000000, 99999999),
                'user_type' => 'company',
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $companyIds[] = DB::table('companies')->insertGetId([
                'user_id' => $userId,
                'name_company' => "Company {$i}",
                'description' => "Test company description {$i}",
                'logo' => null,
                'has_3d_access' => false,
                'model_3d_expires_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Company Cars
        |--------------------------------------------------------------------------
        |
        | توزيع السيارات بالتسلسل:
        | السيارة 1 للشركة 1، السيارة 2 للشركة 2 ... السيارة 10 للشركة 10،
        | ثم يبدأ التوزيع من الشركة 1 مرة ثانية.
        |
        */

        $cars = [];

        for ($i = 1; $i <= 20; $i++) {
            $companyIndex = ($i - 1) % count($companyIds);
            $companyId = $companyIds[$companyIndex];

            $carId = DB::table('company_cars')->insertGetId([
                'company_id' => $companyId,
                'vehicle_type' => fake()->randomElement([
                    'Van',
                    'Pickup',
                    'Truck',
                    'Motorcycle',
                ]),
                'plate_number' => 'SY-' . str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $cars[] = [
                'id' => $carId,
                'company_id' => $companyId,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Drivers
        |--------------------------------------------------------------------------
        |
        | لا يوجد company_id في drivers.
        | الشركة تُعرف دائماً من company_car_id، لذلك لا يمكن حدوث تضارب.
        | السائق 1 مرتبط بالسيارة 1، والسائق 2 بالسيارة 2، وهكذا.
        |
        */

        $driverIds = [];
        $driverIdsByCompany = [];

        for ($i = 1; $i <= 5; $i++) {
            $car = $cars[$i - 1];

            $userId = DB::table('users')->insertGetId([
                'name' => "Driver User {$i}",
                'email' => "driver{$i}@example.com",
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'latitude' => fake()->randomFloat(7, 33.4000000, 36.4000000),
                'longitude' => fake()->randomFloat(7, 35.5000000, 38.5000000),
                'phone' => '09' . rand(30000000, 99999999),
                'user_type' => 'driver',
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $driverId = DB::table('drivers')->insertGetId([
                'user_id' => $userId,
                'company_car_id' => $car['id'],
                'fcm_token' => null,
                'status' => fake()->randomElement([
                    'available',
                    'busy',
                    'offline',
                ]),
                'current_lat' => fake()->randomFloat(7, 33.4000000, 36.4000000),
                'current_lng' => fake()->randomFloat(7, 35.5000000, 38.5000000),
                'last_location_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $driverIds[] = $driverId;
            $driverIdsByCompany[$car['company_id']][] = $driverId;
        }

        /*
        |--------------------------------------------------------------------------
        | Company Store Pivot
        |--------------------------------------------------------------------------
        */

        foreach ($companyIds as $companyId) {
            $randomStores = fake()->randomElements($storeIds, 8);

            foreach ($randomStores as $storeId) {
                DB::table('company_store')->insertOrIgnore([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'return_days' => rand(0, 30),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */

        $categoryNames = [
            'مواد غذائية',
            'مشروبات',
            'منظفات',
            'ألبسة',
            'إلكترونيات',
            'أدوات منزلية',
            'قرطاسية',
            'عناية شخصية',
            'مستلزمات أطفال',
            'منتجات موسمية',
        ];

        $categoryIds = [];

        foreach ($categoryNames as $name) {
            $categoryIds[] = DB::table('categories')->insertGetId([
                'name' => $name,
                'description' => "قسم {$name}",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Features
        |--------------------------------------------------------------------------
        */

        $featureNames = [
            'الوزن',
            'الحجم',
            'اللون',
            'الطول',
            'العرض',
            'العدد بالكرتونة',
            'بلد المنشأ',
            'مدة الصلاحية',
            'نوع التغليف',
            'النكهة',
            'المقاس',
            'المادة',
            'العلامة التجارية',
            'درجة الجودة',
            'الضمان',
            'السعة',
            'الاستخدام',
            'الرائحة',
            'النوع',
            'التركيز',
        ];

        $featureIds = [];

        foreach ($featureNames as $name) {
            $featureIds[] = DB::table('features')->insertGetId([
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Products
        |--------------------------------------------------------------------------
        */

        $productIds = [];

        for ($i = 1; $i <= 30; $i++) {
            $productIds[] = DB::table('products')->insertGetId([
                'name' => "Product {$i}",
                'description' => "Test product description {$i}",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Product Details
        |--------------------------------------------------------------------------
        */

        $productDetailIds = [];
        $productDetails = [];
        $productDetailsByCompany = [];

        for ($i = 1; $i <= 40; $i++) {
            $companyIndex = ($i - 1) % count($companyIds);
            $companyId = $companyIds[$companyIndex];

            $productDetailId = DB::table('product_details')->insertGetId([
                'product_id' => fake()->randomElement($productIds),
                'company_id' => $companyId,
                'category_id' => fake()->randomElement($categoryIds),
                'status' => fake()->randomElement(['available', 'unavailable']),
                'price' => fake()->randomFloat(2, 10, 5000),
                'min_order_quantity' => rand(1, 10),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $productDetailIds[] = $productDetailId;

            $productDetails[] = [
                'id' => $productDetailId,
                'company_id' => $companyId,
            ];

            $productDetailsByCompany[$companyId][] = $productDetailId;
        }

        /*
        |--------------------------------------------------------------------------
        | Feature Product Details Pivot
        |--------------------------------------------------------------------------
        */

        foreach ($productDetailIds as $productDetailId) {
            $randomFeatures = fake()->randomElements($featureIds, 4);

            foreach ($randomFeatures as $featureId) {
                DB::table('feature_product_details')->insertOrIgnore([
                    'feature_id' => $featureId,
                    'product_detail_id' => $productDetailId,
                    'value' => fake()->randomElement([
                        'صغير',
                        'متوسط',
                        'كبير',
                        rand(1, 50) . ' كغ',
                        rand(1, 24) . ' قطعة',
                        rand(100, 2000) . ' مل',
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Images
        |--------------------------------------------------------------------------
        */

        foreach ($productDetailIds as $productDetailId) {
            for ($i = 1; $i <= 2; $i++) {
                DB::table('images')->insert([
                    'product_detail_id' => $productDetailId,
                    'url' => "product-details/images/product-{$productDetailId}-{$i}.jpg",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Advertisements
        |--------------------------------------------------------------------------
        */

        for ($i = 1; $i <= 20; $i++) {
            $startsAt = now()->subDays(rand(0, 10));
            $endsAt = now()->addDays(rand(5, 30));
            $productDetail = fake()->randomElement($productDetails);

            DB::table('advertisements')->insert([
                'company_id' => $productDetail['company_id'],
                'product_detail_id' => $productDetail['id'],
                'title' => "Special Offer {$i}",
                'description' => "Advertisement description {$i}",
                'image' => "advertisements/ad-{$i}.jpg",
                'price' => rand(10000, 250000),
                'status' => fake()->randomElement([
                    'pending',
                    'active',
                    'rejected',
                    'expired',
                ]),
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Orders + Order Product Details + Payments
        |--------------------------------------------------------------------------
        |
        | كل طلب تجريبي يحتوي منتجات من شركة واحدة فقط.
        | وعند إسناد سائق، يتم اختياره من نفس شركة منتجات الطلب.
        |
        */

        for ($i = 1; $i <= 30; $i++) {
            $status = fake()->randomElement([
                'pending',
                'preparing',
                'delivering',
                'delivered',
                'cancelled',
            ]);

            if (in_array($status, ['delivering', 'delivered'], true)) {
                $eligibleCompanyIds = array_keys($driverIdsByCompany);
                $companyId = fake()->randomElement($eligibleCompanyIds);
                $assignedDriverId = fake()->randomElement($driverIdsByCompany[$companyId]);
            } else {
                $companyId = fake()->randomElement($companyIds);
                $assignedDriverId = null;
            }

            $availableProductDetails = $productDetailsByCompany[$companyId];
            $lineCount = min(rand(1, 4), count($availableProductDetails));
            $selectedProductDetails = fake()->randomElements(
                $availableProductDetails,
                $lineCount
            );

            $lines = [];
            $totalPrice = 0;

            foreach ($selectedProductDetails as $productDetailId) {
                $price = rand(5000, 100000);
                $quantity = rand(1, 10);
                $discount = rand(0, 5000);

                $lineTotal = max(0, ($price * $quantity) - $discount);
                $totalPrice += $lineTotal;

                $lines[] = [
                    'product_detail_id' => $productDetailId,
                    'price' => $price,
                    'quantity' => $quantity,
                    'discount' => $discount,
                ];
            }

            $paidAmount = fake()->randomElement([
                0,
                rand(1000, max(1000, (int) ($totalPrice / 2))),
                $totalPrice,
            ]);

            $remainingAmount = max(0, $totalPrice - $paidAmount);

            $orderId = DB::table('orders')->insertGetId([
                'store_id' => fake()->randomElement($storeIds),
                'driver_id' => $assignedDriverId,
                'total_price' => $totalPrice,
                'date' => now()->subDays(rand(0, 30))->toDateString(),
                'commission' => rand(0, 10000),
                'status' => $status,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($lines as $line) {
                DB::table('order_product_detail')->insert([
                    'order_id' => $orderId,
                    'product_detail_id' => $line['product_detail_id'],
                    'discount' => $line['discount'],
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ($paidAmount > 0) {
                DB::table('payments')->insert([
                    'order_id' => $orderId,
                    'amount' => $paidAmount,
                    'paid_at' => now()->subDays(rand(0, 10)),
                    'note' => 'دفعة تجريبية',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
