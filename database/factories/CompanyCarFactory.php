<?php

namespace Database\Factories;

use App\Models\CompanyCar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyCar>
 */
class CompanyCarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vehicle_type' => fake()->randomElement(['فان', 'بيك أب', 'شاحنة صغيرة']),
            'plate_number' => fake()->unique()->bothify('CAR-####'),
            'max_load_kg' => fake()->randomElement([500, 750, 1000, 1500, 2500, 5000]),
        ];
    }
}
