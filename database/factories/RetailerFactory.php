<?php

namespace Database\Factories;

use App\Models\Retailer;
use App\Models\User; // Import User model
use App\Models\Distributor; // Import Distributor model
use App\Models\FieldStaff; // Import FieldStaff model
use App\Models\SalesManager; // Import SalesManager model
use Illuminate\Database\Eloquent\Factories\Factory;

class RetailerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Retailer::class;

    /**
     * Define the model's default state..
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->retailer(),
            'distributor_id' => Distributor::factory(),
            'field_staff_id' => FieldStaff::factory(),
            'sales_manager_id' => SalesManager::factory(),
            'shop_name' => $this->faker->company,
            'proprietor_name' => $this->faker->name,
            'contact_no' => $this->faker->phoneNumber,
            'gst' => $this->faker->regexify('[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}'),
            'address' => $this->faker->address,
            'pincode' => $this->faker->postcode,
            'credit_limit' => $this->faker->randomFloat(2, 0, 100000),
            'latitude' => $this->faker->latitude(8, 37),
            'longitude' => $this->faker->longitude(68, 97),
        ];
    }
}
