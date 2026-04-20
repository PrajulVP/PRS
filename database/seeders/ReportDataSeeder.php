<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Retailer;
use App\Models\Distributor;
use App\Models\FieldStaff;
use App\Models\Product;
use App\Models\District;
use App\Models\Area;
use App\Models\RetailerOrder;
use App\Models\RetailerOrderItem;
use App\Models\RetailerOrderItemBatch;
use App\Models\DistributorOrder;
use App\Models\DistributorOrderItem;
use App\Models\SalesManager;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReportDataSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        if ($products->isEmpty()) {
            $this->command->warn('No products found. Skipping order seeding.');
            return;
        }

        $retailerRole = Role::firstOrCreate(['name' => 'retailer']);
        $distributorRole = Role::firstOrCreate(['name' => 'distributor']);
        $fieldStaffRole = Role::firstOrCreate(['name' => 'fieldstaff']);

        $districts = District::all();
        $areas = Area::all();

        // 1. Ensure enough Distributors (with Kerala names)
        $keralaDistributorNames = [
            'Kerala Pharma Distributors',
            'Cochin Drug Agency',
            'Calicut Medical Supplies',
            'South Kerala Distributors',
            'Malabar Trading Corp'
        ];

        foreach ($keralaDistributorNames as $name) {
            if (Distributor::where('name', $name)->exists()) continue;

            $user = User::factory()->create([
                'name' => $name,
                'role' => 'distributor'
            ]);
            $user->assignRole($distributorRole);

            Distributor::create([
                'user_id' => $user->id,
                'name' => $name,
                'gst' => fake()->bothify('??##########?Z?'),
                'contact_no' => fake()->phoneNumber,
                'address' => fake()->address,
                'district_id' => $districts->isNotEmpty() ? $districts->random()->id : null,
                'area_id' => $areas->isNotEmpty() ? $areas->random()->id : null,
            ]);
        }

        $distributors = Distributor::all();

        // 2. Ensure enough Field Staff
        if (FieldStaff::count() < 5) {
            for ($i = 0; $i < 5; $i++) {
                $user = User::factory()->create(['role' => 'fieldstaff']);
                $user->assignRole($fieldStaffRole);
                FieldStaff::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'phone' => fake()->phoneNumber,
                    'is_active' => true
                ]);
            }
        }
        $fieldStaff = FieldStaff::all();
        $salesManagers = SalesManager::all();

        // 3. Ensure enough Retailers (with Kerala names)
        $keralaRetailerNames = [
            'Mubarak Medicals, Calicut',
            'Care & Cure Pharmacy, Kochi',
            'St. Mary\'s Drug House, Kottayam',
            'Malabar Pharma, Malappuram',
            'Travancore Meds, Thiruvananthapuram',
            'Vellore Medicals, Palakkad',
            'Kairali Medicos, Thrissur',
            'High Range Pharmacy, Idukki',
            'Wayanad Drug Center',
            'Alappuzha Healthcare'
        ];

        foreach ($keralaRetailerNames as $shopName) {
            if (Retailer::where('shop_name', $shopName)->exists()) continue;

            $proprietor = fake()->name;
            $user = User::factory()->create([
                'name' => $proprietor,
                'role' => 'retailer'
            ]);
            $user->assignRole($retailerRole);

            Retailer::create([
                'user_id' => $user->id,
                'shop_name' => $shopName,
                'contact_no' => fake()->phoneNumber,
                'address' => fake()->address,
                'gst' => fake()->bothify('??##########?Z?'),
                'drug_license_no' => fake()->bothify('KL-??-######'),
                'pincode' => $areas->isNotEmpty() ? $areas->random()->pincode : '670001',
                'district_id' => $districts->isNotEmpty() ? $districts->random()->id : null,
                'area_id' => $areas->isNotEmpty() ? $areas->random()->id : null,
                'distributor_id' => $distributors->random()->id,
                'field_staff_id' => $fieldStaff->random()->id,
                'sales_manager_id' => $salesManagers->isNotEmpty() ? $salesManagers->random()->id : null,
            ]);
        }

        $retailers = Retailer::all();

        // 4. Seed Retailer Orders
        for ($i = 0; $i < 30; $i++) {
            if ($retailers->isEmpty()) break;
            $retailer = $retailers->random();
            $status = fake()->randomElement(['pending', 'processing', 'approved', 'delivered', 'cancelled']);
            $placedAt = Carbon::now()->subDays(rand(0, 30));

            $order = RetailerOrder::create([
                'retailer_id' => $retailer->id,
                'distributor_id' => $retailer->distributor_id,
                'fieldstaff_id' => $retailer->field_staff_id,
                'status' => $status,
                'placed_at' => $placedAt,
                'delivered_at' => $status === 'delivered' ? $placedAt->copy()->addDays(rand(1, 3)) : null,
                'total_amount' => 0,
                'total_items' => 0,
                'total_quantity' => 0,
                'payment_status' => $status === 'delivered' ? 'paid' : 'pending'
            ]);

            $totalAmount = 0;
            $totalQty = 0;
            $itemsCount = rand(2, 6);
            $selectedProducts = $products->random(min($itemsCount, $products->count()));

            foreach ($selectedProducts as $product) {
                $qty = rand(5, 50);
                $price = $product->mrp ?: rand(100, 1000);
                $subtotal = $qty * $price;

                $item = RetailerOrderItem::create([
                    'retailer_order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total_amount' => $subtotal,
                ]);

                // Seed some batches if approved or delivered
                if (in_array($status, ['approved', 'delivered'])) {
                    RetailerOrderItemBatch::create([
                        'retailer_order_item_id' => $item->id,
                        'batch_no' => 'B' . strtoupper(fake()->bothify('??###')),
                        'expiry_date' => Carbon::now()->addMonths(rand(6, 24))->format('Y-m-d'),
                        'quantity' => $qty
                    ]);
                }

                $totalAmount += $subtotal;
                $totalQty += $qty;
            }

            $order->update([
                'total_amount' => $totalAmount,
                'total_items' => $selectedProducts->count(),
                'total_quantity' => $totalQty
            ]);
        }

        // 5. Seed Distributor Orders
        for ($i = 0; $i < 15; $i++) {
            if ($distributors->isEmpty()) break;
            $distributor = $distributors->random();
            $status = fake()->randomElement(['pending', 'approved', 'delivered', 'cancelled']);
            $placedAt = Carbon::now()->subDays(rand(0, 30));

            $order = DistributorOrder::create([
                'distributor_id' => $distributor->id,
                'sales_manager_id' => $salesManagers->isNotEmpty() ? $salesManagers->random()->id : null,
                'status' => $status,
                'placed_at' => $placedAt,
                'total_amount' => 0,
                'total_items' => 0,
                'total_quantity' => 0,
                'payment_status' => $status === 'delivered' ? 'paid' : 'pending'
            ]);

            $totalAmount = 0;
            $totalQty = 0;
            $itemsCount = rand(3, 8);
            $selectedProducts = $products->random(min($itemsCount, $products->count()));

            foreach ($selectedProducts as $product) {
                $qty = rand(100, 500);
                $price = ($product->mrp ?: rand(100, 1000)) * 0.8; // Distributor price is lower
                $subtotal = $qty * $price;

                DistributorOrderItem::create([
                    'distributor_order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
                $totalQty += $qty;
            }

            $order->update([
                'total_amount' => $totalAmount,
                'total_items' => $selectedProducts->count(),
                'total_quantity' => $totalQty
            ]);
        }

        $this->command->info('ReportDataSeeder completed successfully with Kerala-themed data.');
    }
}
