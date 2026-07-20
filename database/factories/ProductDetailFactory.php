<?php

namespace Database\Factories;

use App\Models\ProductDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductDetail>
 */
class ProductDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price' => fake()->randomFloat(2, 1000, 500000),
            'min_order_quantity' => fake()->numberBetween(1, 10),
            'package_weight_kg' => fake()->randomFloat(3, 0.25, 50),
            'status' => 'available',
        ];
    }
}
