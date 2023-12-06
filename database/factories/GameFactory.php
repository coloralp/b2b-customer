<?php

namespace Database\Factories;

use App\Enums\CategoryTypeEnum;
use App\Enums\RoleEnum;
use App\Models\Category;
use App\Models\Language;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    protected $model = \App\Models\Game::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categoryTypes = array_column(CategoryTypeEnum::cases(), 'value');
        $length = count($categoryTypes);
        return [
            'uuid' => Str::uuid()->toString(),
            'name' => $this->faker->unique()->name,
            'category_id' => Category::inRandomOrder()->first()->id,
            'publisher_id' => User::role(RoleEnum::PUBLISHER->value)->inRandomOrder()->first()->id,
            'status' => $this->faker->randomElement([1, 2]),
            'min_sales' => $this->faker->randomNumber(),
            'region_id' => Region::inRandomOrder()->first()->id,
            'language_id' => Language::inRandomOrder()->first()->id,
            'category_type' => $categoryTypes(rand(0, $length - 1)),
            'description' => $this->faker->text,
            'stock' => $this->faker->randomFloat(2, 0, 999999.99),
            'updated_at' => now(),
            'created_at' => now(),
        ];
    }
}
