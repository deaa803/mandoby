<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Always seed the complete categories/features catalog first.
        $this->call([
            MarketplaceTaxonomySeeder::class,
        ]);

        // Preserve the existing Shopify product/company seeder.
        // It runs only when its CSV import file exists, so db:seed does not fail
        // on machines that do not have the local import assets.
        $csvPath = Storage::disk('public')->path(
            'imports/shopify_products_250/products.csv'
        );

        if (is_file($csvPath)) {
            $this->call([
                ShopifyCompanyProductsSeeder::class,
            ]);
        } else {
            $this->command?->warn(
                'Shopify products seeder skipped: products.csv was not found.'
            );
        }
    }
}
