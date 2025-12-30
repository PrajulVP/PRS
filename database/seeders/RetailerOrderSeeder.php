<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RetailerOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $retailers = \App\Models\Retailer::all();
        $products = \App\Models\Product::all();

        if ($retailers->isEmpty() || $products->isEmpty()) {
            return;
        }

        foreach ($retailers as $retailer) {
            // Create 3-5 orders for each retailer
            for ($i = 0; $i < rand(3, 5); $i++) {
                $status = fake()->randomElement(['pending', 'accepted_by_sales_manager', 'delivered', 'rejected']);

                $order = \App\Models\RetailerOrder::create([
                    'distributor_id' => $retailer->distributor_id ?? \App\Models\Distributor::factory(),
                    'retailer_id' => $retailer->id,
                    'total_amount' => 0, // Will update after adding items
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'status' => $status,
                    'placed_at' => fake()->dateTimeBetween('-1 month'),
                    'delivered_at' => $status === 'delivered' ? fake()->dateTimeBetween('-1 month') : null,
                    'notes' => fake()->optional()->sentence,
                ]);

                $totalAmount = 0;
                $totalItems = 0;
                $totalQuantity = 0;

                // Add 2-5 items to each order
                $randomProducts = $products->random(rand(2, 5));

                foreach ($randomProducts as $product) {
                    $qty = rand(1, 10);
                    $price = $product->mrp;
                    $itemTotal = $qty * $price;

                    \App\Models\RetailerOrderItem::create([
                        'retailer_order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'total_amount' => $itemTotal,
                    ]);

                    $totalAmount += $itemTotal;
                    $totalQuantity += $qty;
                    $totalItems++;
                }

                $order->update([
                    'total_amount' => $totalAmount,
                    'total_items' => $totalItems,
                    'total_quantity' => $totalQuantity,
                ]);
            }
        }
    }
}
