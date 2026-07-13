<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Company;
use App\Models\Image;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ShopifyCompanyProductsSeeder extends Seeder
{
    private const CSV_PATH = 'imports/shopify_products_250/products.csv';
    private const IMPORT_ROOT = 'imports/shopify_products_250';

    private array $companies = [
        'منزل وحديقة' => [
            'owner' => 'إدارة بيت الشام',
            'name' => 'بيت الشام للمنزل والحديقة',
            'email' => 'company1@example.com',
            'phone' => '0933555101',
            'address' => 'دمشق - المزة',
            'description' => 'شركة متخصصة بمنتجات المنزل والحديقة.',
        ],

        'ألبسة وإكسسوارات' => [
            'owner' => 'إدارة أناقة الشام',
            'name' => 'أناقة الشام للألبسة والإكسسوارات',
            'email' => 'company2@example.com',
            'phone' => '0933555102',
            'address' => 'دمشق - الصالحية',
            'description' => 'شركة متخصصة بالألبسة والإكسسوارات.',
        ],

        'إلكترونيات' => [
            'owner' => 'إدارة أوغاريت التقنية',
            'name' => 'أوغاريت للإلكترونيات',
            'email' => 'company3@example.com',
            'phone' => '0933555103',
            'address' => 'دمشق - البرامكة',
            'description' => 'شركة متخصصة بالإلكترونيات وملحقاتها.',
        ],

        'أطفال' => [
            'owner' => 'إدارة براعم',
            'name' => 'براعم لمستلزمات الأطفال',
            'email' => 'company4@example.com',
            'phone' => '0933555104',
            'address' => 'دمشق - المالكي',
            'description' => 'شركة متخصصة بمنتجات ومستلزمات الأطفال.',
        ],

        'أثاث' => [
            'owner' => 'إدارة ركن البيت',
            'name' => 'ركن البيت للأثاث',
            'email' => 'company5@example.com',
            'phone' => '0933555105',
            'address' => 'دمشق - كفرسوسة',
            'description' => 'شركة متخصصة بالأثاث ومستلزمات التجهيز.',
        ],

        'ألعاب' => [
            'owner' => 'إدارة عالم المرح',
            'name' => 'عالم المرح للألعاب',
            'email' => 'company6@example.com',
            'phone' => '0933555106',
            'address' => 'دمشق - مشروع دمر',
            'description' => 'شركة متخصصة بألعاب الأطفال والألعاب التعليمية.',
        ],

        'قرطاسية' => [
            'owner' => 'إدارة القلم',
            'name' => 'القلم للقرطاسية',
            'email' => 'company7@example.com',
            'phone' => '0933555107',
            'address' => 'دمشق - ركن الدين',
            'description' => 'شركة متخصصة بالقرطاسية ومستلزمات المكاتب.',
        ],

        'كاميرات وبصريات' => [
            'owner' => 'إدارة عدسة الشرق',
            'name' => 'عدسة الشرق للكاميرات والبصريات',
            'email' => 'company8@example.com',
            'phone' => '0933555108',
            'address' => 'دمشق - البحصة',
            'description' => 'شركة متخصصة بالكاميرات والعدسات والبصريات.',
        ],

        'مستلزمات الحيوانات' => [
            'owner' => 'إدارة أليف',
            'name' => 'أليف لمستلزمات الحيوانات',
            'email' => 'company9@example.com',
            'phone' => '0933555109',
            'address' => 'دمشق - أبو رمانة',
            'description' => 'شركة متخصصة بمستلزمات الحيوانات الأليفة.',
        ],
    ];

    public function run(): void
    {
        $csvFullPath = Storage::disk('public')->path(self::CSV_PATH);

        if (!is_file($csvFullPath)) {
            throw new RuntimeException(
                "CSV file not found: {$csvFullPath}"
            );
        }

        DB::transaction(function () use ($csvFullPath): void {
            $companyIds = $this->createCompanies();
            $categoryIds = $this->createCategories();

            $this->importProducts(
                $csvFullPath,
                $companyIds,
                $categoryIds
            );
        });

        $this->command?->info(
            'Imported 225 products and linked 25 products to each company.'
        );

        $this->command?->info(
            'Company accounts: company1@example.com to company9@example.com'
        );

        $this->command?->info(
            'Default password: password'
        );
    }

    private function createCompanies(): array
    {
        $companyIds = [];

        foreach ($this->companies as $categoryName => $data) {
            $user = User::updateOrCreate(
                [
                    'email' => $data['email'],
                ],
                [
                    'name' => $data['owner'],
                    'password' => Hash::make('password'),
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'latitude' => 33.5138,
                    'longitude' => 36.2765,
                    'user_type' => 'company',
                    'email_verified_at' => now(),
                ]
            );

            $company = Company::updateOrCreate(
                [
                    'user_id' => $user->id,
                ],
                [
                    'name_company' => $data['name'],
                    'description' => $data['description'],
                    'delivery_radius_km' => 10,
                    'extra_delivery_fee_per_km' => 1000,
                ]
            );

            $companyIds[$categoryName] = $company->id;
        }

        return $companyIds;
    }

    private function createCategories(): array
    {
        $categoryIds = [];

        foreach (array_keys($this->companies) as $categoryName) {
            $category = Category::firstOrCreate(
                [
                    'name' => $categoryName,
                ],
                [
                    'description' => "منتجات {$categoryName}",
                ]
            );

            $categoryIds[$categoryName] = $category->id;
        }

        return $categoryIds;
    }

    private function importProducts(
        string $csvFullPath,
        array $companyIds,
        array $categoryIds
    ): void {
        $handle = fopen($csvFullPath, 'rb');

        if ($handle === false) {
            throw new RuntimeException(
                'Unable to open products CSV file.'
            );
        }

        try {
            $headers = fgetcsv($handle);

            if ($headers === false) {
                throw new RuntimeException(
                    'Products CSV file is empty.'
                );
            }

            $headers[0] = preg_replace(
                '/^\xEF\xBB\xBF/',
                '',
                $headers[0]
            );

            $importedPerCategory = array_fill_keys(
                array_keys($this->companies),
                0
            );

            $rowNumber = 1;

            while (($values = fgetcsv($handle)) !== false) {
                $rowNumber++;

                if (count($headers) !== count($values)) {
                    $this->command?->warn(
                        "Skipped malformed CSV row {$rowNumber}."
                    );

                    continue;
                }

                $row = array_combine($headers, $values);

                $categoryName = $this->normalizeCategory(
                    trim($row['category'] ?? '')
                );

                if (
                    !isset($companyIds[$categoryName]) ||
                    !isset($categoryIds[$categoryName])
                ) {
                    $this->command?->warn(
                        "Skipped unsupported category at row {$rowNumber}: {$categoryName}"
                    );

                    continue;
                }

                if ($importedPerCategory[$categoryName] >= 25) {
                    continue;
                }

                $productName = trim($row['name'] ?? '');

                if ($productName === '') {
                    continue;
                }

                $description = trim(
                    $row['description'] ?? ''
                );

                $product = Product::firstOrCreate(
                    [
                        'name' => $productName,
                    ],
                    [
                        'description' => $description !== ''
                            ? $description
                            : null,
                    ]
                );

                $productDetail = ProductDetail::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'company_id' => $companyIds[$categoryName],
                    ],
                    [
                        'category_id' => $categoryIds[$categoryName],
                        'status' => 'available',
                        'price' => $this->priceFor(
                            $rowNumber,
                            $categoryName
                        ),
                        'min_order_quantity' => (($rowNumber - 1) % 5) + 1,
                    ]
                );

                $relativeImage = ltrim(
                    str_replace(
                        '\\',
                        '/',
                        trim($row['image'] ?? '')
                    ),
                    '/'
                );

                if ($relativeImage !== '') {
                    $imageUrl = self::IMPORT_ROOT
                        . '/'
                        . $relativeImage;

                    Image::updateOrCreate(
                        [
                            'product_detail_id' => $productDetail->id,
                            'url' => $imageUrl,
                        ],
                        []
                    );
                }

                $importedPerCategory[$categoryName]++;
            }

            foreach ($importedPerCategory as $categoryName => $count) {
                if ($count !== 25) {
                    throw new RuntimeException(
                        "Category {$categoryName} imported {$count} products instead of 25."
                    );
                }
            }
        } finally {
            fclose($handle);
        }
    }

    private function normalizeCategory(
        string $categoryName
    ): string {
        return match ($categoryName) {
            'قرطاسية ولوازم مكتبية' => 'قرطاسية',
            default => $categoryName,
        };
    }

    private function priceFor(
        int $rowNumber,
        string $categoryName
    ): int {
        $basePrices = [
            'منزل وحديقة' => 35000,
            'ألبسة وإكسسوارات' => 45000,
            'إلكترونيات' => 85000,
            'أطفال' => 30000,
            'أثاث' => 150000,
            'ألعاب' => 25000,
            'قرطاسية' => 8000,
            'كاميرات وبصريات' => 125000,
            'مستلزمات الحيوانات' => 22000,
        ];

        return $basePrices[$categoryName]
            + (($rowNumber % 10) * 5000);
    }
}
