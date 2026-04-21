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
            'AS-F01' => 100, // Wrist splint
        ];

        $distributors = \App\Models\Distributor::all();

        foreach ($productsToSeed as $productCode => $stockCount) {
            $product = \App\Models\Product::where('product_code', $productCode)->first();
            if (!$product) {
                echo "Warning: Product with code $productCode not found. Skipping...\n";
                continue;
            }

            echo "Seeding inventory for: {$product->product_name} (Code: $productCode)\n";

            $variants = $product->variant_options ?? [];
            $sides = $variants['Side'] ?? [null];
            $sizes = $variants['Size'] ?? [null];

            foreach ($distributors as $distributor) {
                foreach ($sides as $side) {
                    foreach ($sizes as $size) {
                        // Generate a unique code for this variant combination
                        $variantCode = $product->product_code;
                        if ($side) $variantCode .= '-' . substr($side, 0, 1);
                        if ($size) $variantCode .= '-' . $size;

                        \Illuminate\Support\Facades\DB::table('inventories')->updateOrInsert(
                            [
                                'product_id' => $product->id,
                                'distributor_id' => $distributor->id,
                                'side' => $side,
                                'size' => $size,
                            ],
                            [
                                'distributor_product_code' => $variantCode,
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
    }
}
