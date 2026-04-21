<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PrescriptionLog;
use App\Models\Retailer;
use App\Models\Product;
use App\Models\RetailerOrder;
use App\Models\RetailerOrderItem;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AnalyticsSeeder extends Seeder
{
    public function run()
    {
        $retailers = Retailer::all();
        $molecules = [
            'PARACETAMOL', 'AMOXICILLIN', 'AZITHROMYCIN', 'PANTOPRAZOLE', 
            'METFORMIN', 'ATORVASTATIN', 'IBUPROFEN', 'CETIRIZINE', 
            'OMEPRAZOLE', 'TELMISARTAN', 'GLIMEPIRIDE', 'AMLODIPINE'
        ];

        // 1. Seed Prescription Logs (AI Demand)
        for ($i = 0; $i < 100; $i++) {
            $retailer = $retailers->random();
            $numMeds = rand(1, 4);
            $extractedMeds = [];
            
            for ($j = 0; $j < $numMeds; $j++) {
                $extractedMeds[] = [
                    'name' => $molecules[array_rand($molecules)],
                    'dosage' => rand(250, 500) . 'mg',
                    'confidence' => rand(85, 99) / 100
                ];
            }

            PrescriptionLog::create([
                'retailer_id' => $retailer->id,
                'raw_text' => 'Sample prescription OCR text for ' . $retailer->name,
                'extracted_data' => ['medicines' => $extractedMeds],
                'created_at' => Carbon::now()->subDays(rand(0, 30))
            ]);
        }

        // 2. Seed Sales Data (Fulfillment)
        $products = Product::whereNotNull('generic_name')->get();
        if ($products->count() > 0) {
            foreach ($retailers as $retailer) {
                for ($k = 0; $k < 5; $k++) {
                    $order = RetailerOrder::create([
                        'retailer_id' => $retailer->id,
                        'order_code' => 'DUMMY-' . strtoupper(Str::random(8)),
                        'status' => 'delivered',
                        'total_amount' => rand(1000, 5000),
                        'placed_at' => Carbon::now()->subDays(rand(0, 20)),
                        'delivered_at' => Carbon::now()->subDays(rand(0, 5))
                    ]);

                    $numItems = rand(1, 3);
                    for ($m = 0; $m < $numItems; $m++) {
                        $product = $products->random();
                        RetailerOrderItem::create([
                            'retailer_order_id' => $order->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'side' => ['Left', 'Right', 'Both'][rand(0, 2)],
                            'size' => ['S', 'M', 'L'][rand(0, 2)],
                            'quantity' => rand(5, 50),
                            'unit' => 'Units',
                            'unit_price' => rand(50, 200),
                            'total_amount' => 0 // Calculated usually
                        ]);
                    }
                }
            }
        }
    }
}
