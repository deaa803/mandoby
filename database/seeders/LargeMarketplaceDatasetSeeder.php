<?php

namespace Database\Seeders;

use Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use JsonException;
use RuntimeException;

class LargeMarketplaceDatasetSeeder extends Seeder
{
    private const COMPANIES_FILE = 'data/large_marketplace/companies.csv';
    private const PRODUCTS_FILE = 'imports/large_marketplace_1000/products.csv';
    private const COMPANY_COUNT = 50;

    public function run(): void
    {
        $this->ensureRequiredSchema();
        $this->call(MarketplaceTaxonomySeeder::class);

        $productsCount = DB::transaction(function (): int {
            $this->resetGeneratedDataset();

            $categories = DB::table('categories')->pluck('id', 'name')->all();
            $features = DB::table('features')->pluck('id', 'name')->all();
            $companies = $this->seedCompanies($categories);

            return $this->seedProducts(
                $companies,
                $categories,
                $features,
                Storage::disk('public')->path(self::PRODUCTS_FILE),
            );
        });

        $this->command?->info(
            "تمت إضافة 50 شركة و{$productsCount} منتج مع الصور المحلية وأوزان الطرود."
        );
        $this->command?->info('الحسابات: company1@example.com إلى company50@example.com');
        $this->command?->info('كلمة المرور: password');
    }

    private function ensureRequiredSchema(): void
    {
        if (!Schema::hasColumn('product_details', 'package_weight_kg')) {
            throw new RuntimeException(
                'عمود package_weight_kg غير موجود. نفّذ php artisan migrate أولاً.'
            );
        }
    }

    private function resetGeneratedDataset(): void
    {
        $emails = [];

        for ($index = 1; $index <= self::COMPANY_COUNT; $index++) {
            $emails[] = "company{$index}@example.com";
        }

        $userIds = DB::table('users')
            ->where('user_type', 'company')
            ->whereIn('email', $emails)
            ->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        $companyIds = DB::table('companies')
            ->whereIn('user_id', $userIds)
            ->pluck('id');

        $productIds = DB::table('product_details')
            ->whereIn('company_id', $companyIds)
            ->pluck('product_id')
            ->unique()
            ->values();

        if (Schema::hasTable('company_cars') && Schema::hasTable('drivers')) {
            $carIds = DB::table('company_cars')
                ->whereIn('company_id', $companyIds)
                ->pluck('id');

            if ($carIds->isNotEmpty()) {
                DB::table('drivers')->whereIn('company_car_id', $carIds)->delete();
            }
        }

        DB::table('users')->whereIn('id', $userIds)->delete();

        if ($productIds->isNotEmpty()) {
            DB::table('products')
                ->whereIn('id', $productIds)
                ->whereNotExists(function ($query): void {
                    $query->selectRaw('1')
                        ->from('product_details')
                        ->whereColumn('product_details.product_id', 'products.id');
                })
                ->delete();
        }
    }

    private function seedCompanies(array $categories): array
    {
        $companies = [];
        $passwordHashes = [];
        $now = now();

        foreach ($this->readCsv(database_path(self::COMPANIES_FILE)) as $row) {
            $categoryName = $this->required($row, 'category');
            $email = $this->required($row, 'email');

            if (!isset($categories[$categoryName])) {
                throw new RuntimeException("تصنيف الشركة غير موجود: {$categoryName}");
            }

            $password = $this->required($row, 'password');
            $passwordHashes[$password] ??= Hash::make($password);

            $userId = DB::table('users')->insertGetId([
                'name' => $this->required($row, 'owner_name'),
                'email' => $email,
                'email_verified_at' => $now,
                'password' => $passwordHashes[$password],
                'latitude' => $this->decimal($row, 'latitude'),
                'longitude' => $this->decimal($row, 'longitude'),
                'phone' => $this->required($row, 'phone'),
                'address' => $this->required($row, 'address'),
                'user_type' => 'company',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $companyId = DB::table('companies')->insertGetId([
                'user_id' => $userId,
                'name_company' => $this->required($row, 'company_name'),
                'description' => $this->required($row, 'description'),
                'logo' => $this->localPublicPath($row, 'logo_url'),
                'delivery_radius_km' => $this->decimal($row, 'delivery_radius_km'),
                'extra_delivery_fee_per_km' => $this->decimal(
                    $row,
                    'extra_delivery_fee_per_km',
                ),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $companies[$email] = [
                'id' => $companyId,
                'category' => $categoryName,
                'specialty' => $this->required($row, 'specialty'),
            ];
        }

        if (count($companies) !== self::COMPANY_COUNT) {
            throw new RuntimeException('ملف الشركات يجب أن يحتوي على 50 شركة تماماً.');
        }

        return $companies;
    }

    private function seedProducts(
        array $companies,
        array $categories,
        array $features,
        string $productsFile,
    ): int {
        $featureRows = [];
        $imageRows = [];
        $discountRows = [];
        $productsCount = 0;
        $companyProductCounts = array_fill_keys(array_keys($companies), 0);
        $now = now();

        foreach ($this->readCsv($productsFile) as $row) {
            $companyEmail = $this->required($row, 'company_email');
            $categoryName = $this->required($row, 'category');

            if (!isset($companies[$companyEmail])) {
                throw new RuntimeException("شركة المنتج غير معروفة: {$companyEmail}");
            }

            if ($companies[$companyEmail]['category'] !== $categoryName) {
                throw new RuntimeException(
                    "الشركة {$companyEmail} لا يمكنها بيع منتجات من {$categoryName}."
                );
            }

            if (!isset($categories[$categoryName])) {
                throw new RuntimeException("تصنيف المنتج غير موجود: {$categoryName}");
            }

            $productId = DB::table('products')->insertGetId([
                'name' => $this->required($row, 'name'),
                'description' => $this->required($row, 'description'),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $productDetailId = DB::table('product_details')->insertGetId([
                'product_id' => $productId,
                'company_id' => $companies[$companyEmail]['id'],
                'category_id' => $categories[$categoryName],
                'status' => $this->status($row),
                'price' => $this->decimal($row, 'price'),
                'min_order_quantity' => $this->positiveInteger(
                    $row,
                    'min_order_quantity',
                ),
                'package_weight_kg' => $this->positiveDecimal(
                    $row,
                    'package_weight_kg',
                ),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $imageRows[] = [
                'product_detail_id' => $productDetailId,
                'url' => $this->localPublicPath($row, 'image_url'),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $discountPercentage = $this->decimal($row, 'discount_percentage');

            if ($discountPercentage > 0 && Schema::hasTable('product_discounts')) {
                $discountRows[] = [
                    'product_detail_id' => $productDetailId,
                    'quantity' => $this->positiveInteger($row, 'discount_quantity'),
                    'discount_percentage' => $discountPercentage,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach ($this->decodeFeatures($row) as $featureName => $value) {
                if (!isset($features[$featureName])) {
                    throw new RuntimeException("ميزة غير معروفة: {$featureName}");
                }

                if (mb_strlen($value) > 255) {
                    throw new RuntimeException("قيمة الميزة طويلة جداً: {$featureName}");
                }

                $featureRows[] = [
                    'feature_id' => $features[$featureName],
                    'product_detail_id' => $productDetailId,
                    'value' => $value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $productsCount++;
            $companyProductCounts[$companyEmail]++;
        }

        if ($productsCount === 0) {
            throw new RuntimeException('ملف المنتجات لا يحتوي على أي منتج.');
        }

        $this->insertInChunks('images', $imageRows);
        $this->insertInChunks('feature_product_details', $featureRows);

        if ($discountRows !== []) {
            $this->insertInChunks('product_discounts', $discountRows);
        }

        return $productsCount;
    }

    private function readCsv(string $path): Generator
    {
        if (!is_file($path)) {
            throw new RuntimeException("ملف الداتا غير موجود: {$path}");
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException("تعذر فتح ملف الداتا: {$path}");
        }

        try {
            $headers = fgetcsv($handle);

            if ($headers === false) {
                throw new RuntimeException("ملف الداتا فارغ: {$path}");
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
            $line = 1;

            while (($values = fgetcsv($handle)) !== false) {
                $line++;

                if ($values === [null]) {
                    continue;
                }

                if (count($headers) !== count($values)) {
                    throw new RuntimeException("سطر CSV غير صالح {$line} في {$path}");
                }

                yield array_combine($headers, $values);
            }
        } finally {
            fclose($handle);
        }
    }

    private function decodeFeatures(array $row): array
    {
        try {
            $decoded = json_decode(
                $this->required($row, 'features_json'),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new RuntimeException('قيمة features_json غير صالحة.', 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new RuntimeException('features_json يجب أن يكون كائناً JSON.');
        }

        return array_map(
            static fn (mixed $value): string => (string) $value,
            $decoded,
        );
    }

    private function insertInChunks(string $table, array $rows): void
    {
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    private function required(array $row, string $key): string
    {
        $value = trim((string) ($row[$key] ?? ''));

        if ($value === '') {
            throw new RuntimeException("حقل الداتا مطلوب: {$key}");
        }

        return $value;
    }

    private function decimal(array $row, string $key): float
    {
        $value = $this->required($row, $key);

        if (!is_numeric($value)) {
            throw new RuntimeException("حقل {$key} يجب أن يكون رقمياً.");
        }

        return (float) $value;
    }

    private function positiveDecimal(array $row, string $key): float
    {
        $value = $this->decimal($row, $key);

        if ($value <= 0) {
            throw new RuntimeException("حقل {$key} يجب أن يكون أكبر من صفر.");
        }

        return $value;
    }

    private function positiveInteger(array $row, string $key): int
    {
        $value = (int) $this->decimal($row, $key);

        if ($value < 1) {
            throw new RuntimeException("حقل {$key} يجب ألا يقل عن 1.");
        }

        return $value;
    }

    private function localPublicPath(array $row, string $key): string
    {
        $path = ltrim(str_replace('\\', '/', $this->required($row, $key)), '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            throw new RuntimeException("حقل {$key} يجب أن يحتوي على مسار صورة محلي.");
        }

        if (!Storage::disk('public')->exists($path)) {
            throw new RuntimeException(
                "الصورة المحلية غير موجودة داخل storage/app/public: {$path}"
            );
        }

        return $path;
    }

    private function status(array $row): string
    {
        $status = $this->required($row, 'status');

        if (!in_array($status, ['available', 'unavailable'], true)) {
            throw new RuntimeException("حالة المنتج غير صالحة: {$status}");
        }

        return $status;
    }
}
