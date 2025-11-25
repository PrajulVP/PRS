<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SalesManager>
 */
class SalesManagerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory()->salesmanager(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'contact_no' => $this->faker->phoneNumber(),
        ];
    }
}
