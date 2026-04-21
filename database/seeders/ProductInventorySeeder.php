<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productsToSeed = [
            20 => 100, // Wrist splint
            1 => 500,  // Sudhneelgiri Cough Syrup (example)
            2 => 500,  // Example 2
        ];

        $distributors = \App\Models\Distributor::all();

        foreach ($productsToSeed as $productId => $stockCount) {
            $product = \App\Models\Product::find($productId);
            if (!$product) continue;

            echo "Seeding inventory for: {$product->product_name}\n";

            foreach ($distributors as $distributor) {
                \Illuminate\Support\Facades\DB::table('inventories')->updateOrInsert(
                    [
                        'product_id' => $productId,
                        'distributor_id' => $distributor->id
                    ],
                    [
                        'distributor_product_code' => $product->product_code,
                        'product_name' => $product->product_name,
                        'stock' => $stockCount,
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );
            }
        }
    }
}
