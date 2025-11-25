<?php

namespace Database\Factories;

use App\Models\FieldStaff;
use App\Models\User; // Import User model
use App\Models\SalesManager; // Import SalesManager model
use Illuminate\Database\Eloquent\Factories\Factory;

class FieldStaffFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = FieldStaff::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->fieldstaff(),
            'sales_manager_id' => SalesManager::factory(),
            'pincode' => $this->faker->postcode,
        ];
    }
}