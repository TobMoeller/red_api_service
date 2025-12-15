<?php

namespace Database\Factories;

use App\Enums\Order\Status;
use App\Enums\Order\Type;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'type' => $this->faker->randomElement(Type::cases()),
            'status' => $this->faker->randomElement(Status::cases()),
        ];
    }
}
