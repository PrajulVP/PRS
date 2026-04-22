<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DistributorOrder;
use App\Models\DistributorOrderItem;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\SalesManager;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DistributorOrderSeeder extends Seeder
{
    public function run(): void
    {
        $distributors = Distributor::all();
        $products = Product::all();
        
        if ($distributors->isEmpty()) {
            $this->command->warn('No distributors found. Seed distributors first.');
            return;
        }

        if ($products->isEmpty()) {
            $this->command->warn('No products found. Seed products first.');
            return;
        }

        $statuses = [
            DistributorOrder::STATUS_PENDING,
            DistributorOrder::STATUS_PROCESSING,
            DistributorOrder::STATUS_APPROVED,
            DistributorOrder::STATUS_DELIVERED,
            DistributorOrder::STATUS_CANCELLED,
            DistributorOrder::STATUS_REJECTED,
        ];

        foreach ($distributors as $distributor) {
            foreach ($statuses as $status) {
                // Create one order for each status
                $order = DistributorOrder::create([
                    'distributor_id' => $distributor->id,
                    'sales_manager_id' => $distributor->sales_manager_id ?? SalesManager::first()?->id,
                    'status' => $status,
                    'payment_status' => (in_array($status, ['approved', 'delivered'])) ? 'paid' : 'pending',
                    'placed_at' => Carbon::now()->subDays(rand(0, 30)),
                    'total_amount' => 0,
                    'total_items' => 0,
                    'total_quantity' => 0,
                    'order_code' => 'DO-' . Str::upper(Str::random(6)),
                ]);

                // Clear the 'pending' status set by boot method (if any) and force the requested status
                $order->status = $status;
                $order->save();

                $numItems = rand(2, 5);
                $orderProducts = $products->random($numItems);
                
                $orderTotal = 0;
                $orderQty = 0;

                foreach ($orderProducts as $product) {
                    $qty = rand(1, 10);
                    $unit = 'Strips';
                    $price = (float)($product->pts ?? 100);
                    
                    // Simple multiplication for seeker (ignoring complex box/unit logic for speed)
                    $subtotal = $qty * $price;

                    DistributorOrderItem::create([
                        'distributor_order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->product_name,
                        'quantity' => $qty,
                        'unit' => $unit,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);

                    $orderTotal += $subtotal;
                    $orderQty += $qty;
                }

                $order->update([
                    'total_amount' => $orderTotal,
                    'total_items' => $numItems,
                    'total_quantity' => $orderQty,
                ]);
            }
        }

        $this->command->info('Seeded distributor orders with all statuses for all distributors.');
    }
}
