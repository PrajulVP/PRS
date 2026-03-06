<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RetailerOrder;
use App\Models\RetailerOrderItem;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\Distributor;
use Carbon\Carbon;

class SpecificDistributorOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $status = RetailerOrder::STATUS_PROCESSING;

        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->error("No products found. Please seed products first.");
            return;
        }

        $distributors = Distributor::all();
        if ($distributors->isEmpty()) {
            $this->command->error("No distributors found.");
            return;
        }

        foreach ($distributors as $distributor) {
            $retailers = Retailer::where('distributor_id', $distributor->id)->get();
            if ($retailers->isEmpty()) {
                continue;
            }

            foreach ($retailers as $retailer) {
                // Create 2 processing orders for each retailer to ensure data is visible
                for ($i = 0; $i < 2; $i++) {
                    $order = RetailerOrder::create([
                        'distributor_id' => $distributor->id,
                        'retailer_id' => $retailer->id,
                        'total_amount' => 0,
                        'total_items' => 0,
                        'total_quantity' => 0,
                        'status' => $status,
                        'placed_at' => Carbon::now()->subDays(rand(1, 5)),
                        'notes' => "Seed order for testing $status status for distributor {$distributor->name}.",
                        'fieldstaff_id' => $retailer->fieldstaff_id ?? null,
                    ]);

                    $totalAmount = 0;
                    $totalItems = 0;
                    $totalQuantity = 0;

                    $numItems = rand(2, 5);
                    $randomProducts = $products->random($numItems);

                    foreach ($randomProducts as $product) {
                        $qty = rand(1, 5);
                        $price = $product->mrp ?? 100.00;
                        $itemTotal = $qty * $price;

                        RetailerOrderItem::create([
                            'retailer_order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $qty,
                            'unit' => 'strips',
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
        $this->command->info("Seeded processing orders for all distributors successfully.");
    }
}
