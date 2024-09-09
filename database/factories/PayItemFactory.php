<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PayItem>
 */
class PayItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_id' => Business::factory(),
            'amount' => $this->faker->numberBetween(100, 5000), 
            'hours_worked' => $this->faker->randomFloat(2, 1, 40),
            'pay_rate' => $this->faker->numberBetween(100, 500),
            'date' => $this->faker->date(),
            'external_id' => $this->faker->uuid,
        ];
    }
}
