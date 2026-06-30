<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Company Cars
        |--------------------------------------------------------------------------
        */

        $carIds = [];

        for ($i = 1; $i <= 20; $i++) {
            $carIds[] = DB::table('company_cars')->insertGetId([
                'company_id' => fake()->randomElement($companyIds),
                'vehicle_type' => fake()->randomElement(['Van', 'Pickup', 'Truck', 'Motorcycle']),
                'driver_name' => "Driver Car {$i}",
                'plate_number' => 'SY-' . rand(100000, 999999),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Drivers
        |--------------------------------------------------------------------------
        */

        $driverIds = [];

        for ($i = 1; $i <= 5; $i++) {
            $companyId = fake()->randomElement($companyIds);

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

            $driverIds[] = DB::table('drivers')->insertGetId([
                'user_id' => $userId,
                'company_id' => $companyId,
                'company_car_id' => fake()->randomElement($carIds),
                'status' => fake()->randomElement(['available', 'busy', 'offline']),
                'current_lat' => fake()->randomFloat(7, 33.4000000, 36.4000000),
                'current_lng' => fake()->randomFloat(7, 35.5000000, 38.5000000),
                'last_location_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
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
                'min_order_quantity' => rand(1, 10),
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

        for ($i = 1; $i <= 40; $i++) {
            $productDetailIds[] = DB::table('product_details')->insertGetId([
                'product_id' => fake()->randomElement($productIds),
                'company_id' => fake()->randomElement($companyIds),
                'category_id' => fake()->randomElement($categoryIds),
                'status' => fake()->randomElement(['available', 'unavailable']),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
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

            DB::table('advertisements')->insert([
                'company_id' => fake()->randomElement($companyIds),
                'product_detail_id' => fake()->randomElement($productDetailIds),
                'title' => "Special Offer {$i}",
                'description' => "Advertisement description {$i}",
                'image' => "advertisements/ad-{$i}.jpg",
                'price' => rand(10000, 250000),
                'status' => fake()->randomElement(['pending', 'active', 'rejected', 'expired']),
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
        */

        for ($i = 1; $i <= 30; $i++) {
            $status = fake()->randomElement([
                'pending',
                'preparing',
                'delivering',
                'delivered',
                'cancelled',
            ]);

            $assignedDriverId = in_array($status, ['delivering', 'delivered'])
                ? fake()->randomElement($driverIds)
                : null;

            $selectedProductDetails = fake()->randomElements($productDetailIds, rand(1, 4));

            $lines = [];
            $totalPrice = 0;

            foreach ($selectedProductDetails as $productDetailId) {
                $price = rand(5000, 100000);
                $quantity = rand(1, 10);
                $discount = rand(0, 5000);

                $lineTotal = ($price * $quantity) - $discount;
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
