<?php

namespace Database\Factories;

use App\Models\Area;
use App\Models\District; // Import District model
use Illuminate\Database\Eloquent\Factories\Factory;

class AreaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Area::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->streetName,
            'pincode' => $this->faker->postcode,
            'district_id' => District::inRandomOrder()->first()->id ?? District::factory(), // Use existing District or create one
        ];
    }
}