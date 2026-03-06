<?php

namespace Database\Factories;

use App\Models\RetailerOrder;
use App\Models\Retailer;
use App\Models\Distributor;
use Illuminate\Database\Eloquent\Factories\Factory;

class RetailerOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RetailerOrder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'order_code' => 'RO-' . $this->faker->unique()->regexify('[A-Z0-9]{6}'),
            'distributor_id' => Distributor::factory(),
            'retailer_id' => Retailer::factory(),
            'product_name' => $this->faker->word,
            'sku' => $this->faker->ean8,
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit_price' => $this->faker->randomFloat(2, 10, 1000),
            'total_amount' => $this->faker->randomFloat(2, 100, 5000),
            'status' => $this->faker->randomElement([RetailerOrder::STATUS_PENDING, RetailerOrder::STATUS_PROCESSING, RetailerOrder::STATUS_ACCEPTED, RetailerOrder::STATUS_DELIVERED, RetailerOrder::STATUS_CANCELLED]),
            'placed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'notes' => $this->faker->sentence,
            'field_staff_id' => null, // Will be assigned later in tests
            'delivered_at' => null,    // Will be assigned later in tests
        ];
    }

    /**
     * Indicate that the order is pending.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RetailerOrder::STATUS_PENDING,
            ];
        });
    }

    /**
     * Indicate that the order is accepted by distributor.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function accepted()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RetailerOrder::STATUS_ACCEPTED,
            ];
        });
    }

    /**
     * Indicate that the order is assigned to field staff.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function processing()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RetailerOrder::STATUS_PROCESSING,
            ];
        });
    }

    /**
     * Indicate that the order is delivered.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function delivered()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => RetailerOrder::STATUS_DELIVERED,
                'delivered_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            ];
        });
    }
}
