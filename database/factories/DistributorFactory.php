<?php

namespace Database\Factories;

use App\Models\Distributor;
use App\Models\User; // Import User model
use Illuminate\Database\Eloquent\Factories\Factory;

class DistributorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Distributor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->distributor(), // Creates a User and uses its ID
            'name' => $this->faker->name,
            'gst' => $this->faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}'),
            'drug_license_no' => $this->faker->unique()->bothify('DL#####'),
            'contact_no' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'pincode' => $this->faker->postcode,
            'district_id' => null, // Will be set in the test or by another factory
            'area_id' => null,     // Will be set in the test or by another factory
        ];
    }
}