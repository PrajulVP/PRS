<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'product_code' => $this->faker->unique()->ean8,
            'product_name' => $this->faker->word,
            'generic_name' => $this->faker->word,
            'pack' => $this->faker->word, // Using 'pack' column
            'quantity' => $this->faker->numberBetween(1, 10), // Using 'quantity' column
            'batch_no' => $this->faker->unique()->numberBetween(1000, 9999), // batch_no is integer
            'expiry' => $this->faker->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
            'mrp' => $this->faker->randomFloat(2, 10, 1000),
            'ptr' => $this->faker->randomFloat(2, 5, 900),
            'taxable_value' => $this->faker->randomFloat(2, 5, 800),
            'gst' => $this->faker->randomFloat(2, 5, 20),
            'net_amount' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }
}