<?php

namespace Database\Factories;

use App\Models\Advertisement;
use App\Models\Company;
use App\Models\ProductDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Advertisement>
 */
class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    public function definition(): array
    {
        $startsAt = fake()->dateTimeBetween('-10 days', '+5 days');
        $endsAt = fake()->dateTimeBetween($startsAt, '+30 days');

        return [
            'company_id' => Company::query()->inRandomOrder()->value('id') ?? Company::factory(),
            'product_detail_id' => ProductDetail::query()->inRandomOrder()->value('id'),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'image' => 'advertisements/default-ad.jpg',
            'price' => fake()->randomFloat(2, 10000, 250000),
            'status' => fake()->randomElement(['pending', 'active', 'rejected', 'expired']),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];
    }
}
