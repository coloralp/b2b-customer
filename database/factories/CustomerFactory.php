<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'uuid' => Str::uuid(),
            'currency_balance_remaining_balance' => fake()->randomFloat(),
            'currency_balance_spent_balance' => fake()->randomFloat(),
            'currency_balance' => fake()->randomFloat(),
            'currency' => 1,
            'location' => fake()->address(),
            'name' => fake()->name(),
            'address' => fake()->address,
            'vat_number' => "1555",
            'company_registration_number' => "1555",
            "related_person" => 2,
            "email" => fake()->email,
            "web_site" => "test.com",
            "payment_method" => "kart",
            "balance" => 40,
            "spent_balance" => 10,
            "remaining_balance" => 5,
            "info" => "saıhshasa"
        ];
    }
}
