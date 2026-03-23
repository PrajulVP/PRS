<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Distributor;

class AiService
{
    /**
     * Send a prescription file to the external Python AI API and return the extracted data.
     *
     * @param UploadedFile $file
     * @return array|null
     */
    public function extractPrescription(UploadedFile $file)
    {
        $basePath = env('AI_API_URL', 'http://13.204.159.20');
        $apiUrl = rtrim($basePath, '/') . "/extract-prescription";
        Log::info('Prescription AI API Request', ['url' => $apiUrl]);

        try {
            $response = Http::timeout(60)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($apiUrl);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Prescription AI API Success Response', ['response' => $data]);
                return $data;
            }

            Log::error('Prescription AI API Error Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Prescription AI API Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Match extracted medicines with system products and distributors.
     */
    public function matchExtractedMedicines($aiItems, $retailer = null)
    {
        $matchedOptions = [];
        $outOfStockItems = [];
        $unmatchedItems = [];

        foreach ($aiItems as $medicine) {
            $nameStr = $medicine['name'] ?? $medicine['product_name'] ?? $medicine['description'] ?? null;
            $quantity = $medicine['count'] ?? $medicine['quantity'] ?? $medicine['qty'] ?? 1;
            if (!$nameStr) continue;

            $name = trim($nameStr);
            
            // Find likely matching products (top 5)
            $matchingProducts = Product::where('product_name', 'LIKE', "%{$name}%")
                ->orWhere('generic_name', 'LIKE', "%{$name}%")
                ->take(5)
                ->get();

            if ($matchingProducts->isEmpty()) {
                // Secondary check: search by words if name is complex
                $words = explode(' ', preg_replace('/[^a-z0-9 ]/i', ' ', $name));
                foreach ($words as $word) {
                    if (strlen($word) > 3) {
                        $matchingProducts = Product::where('product_name', 'LIKE', "%{$word}%")
                            ->orWhere('generic_name', 'LIKE', "%{$word}%")
                            ->take(5)
                            ->get();
                        if ($matchingProducts->isNotEmpty()) break;
                    }
                }
            }

            if ($matchingProducts->isEmpty()) {
                $unmatchedItems[] = ['name' => $name, 'quantity' => (int)$quantity];
                continue;
            }

            foreach ($matchingProducts as $product) {
                $distributors = $this->getAvailableDistributors($product, $retailer);
                
                if ($distributors->isEmpty()) {
                    $outOfStockItems[] = [
                        'product_name' => $product->product_name,
                        'generic_name' => $product->generic_name,
                        'original_name' => $name,
                        'quantity' => (int)$quantity
                    ];
                } else {
                    foreach ($distributors as $distributor) {
                        $matchedOptions[] = [
                            'product' => $product,
                            'distributor' => [
                                'id' => $distributor->id,
                                'name' => $distributor->user->name ?? 'N/A',
                                'shop_name' => $distributor->shop_name,
                                'distance' => $distributor->distance,
                                'stock' => $distributor->pivot->stock ?? 0,
                            ],
                            'has_stock' => true,
                            'quantity' => (int)$quantity,
                            'unit' => $this->determineDefaultUnit($product),
                            'original_name' => $name
                        ];
                    }
                }
            }
        }

        return [
            'matched_items' => $matchedOptions,
            'out_of_stock_items' => $outOfStockItems,
            'unmatched_items' => $unmatchedItems
        ];
    }

    protected function getAvailableDistributors($product, $retailer)
    {
        $allDistributors = Distributor::with('user')->get();
        $stockMap = DB::table('inventories')
            ->where('product_id', $product->id)
            ->selectRaw('distributor_id, SUM(stock) as total_stock')
            ->groupBy('distributor_id')
            ->pluck('total_stock', 'distributor_id');

        return $allDistributors->filter(function ($distributor) use ($stockMap) {
            return $stockMap->has($distributor->id);
        })->map(function ($distributor) use ($retailer, $stockMap) {
            $distributor->pivot = (object)['stock' => $stockMap[$distributor->id]];
            if ($retailer && $retailer->latitude && $retailer->longitude && $distributor->latitude && $distributor->longitude) {
                $distributor->distance = $this->calculateDistance(
                    (float)$retailer->latitude, (float)$retailer->longitude,
                    (float)$distributor->latitude, (float)$distributor->longitude
                );
            } else {
                $distributor->distance = null;
            }
            return $distributor;
        })->sort(function ($a, $b) {
            $distA = $a->distance ?? 999999;
            $distB = $b->distance ?? 999999;
            if ($distA != $distB) return $distA <=> $distB;
            return ($b->pivot->stock ?? 0) <=> ($a->pivot->stock ?? 0);
        })->values();
    }

    protected function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    protected function determineDefaultUnit($product)
    {
        $pPack = strtolower($product->pack ?? '');
        $pName = strtolower($product->product_name ?? '');
        $isCount = (isset($product->box_size) && (int)$product->box_size === 1 && isset($product->carton_size) && (int)$product->carton_size === 1) ||
            str_contains($pPack, 'nos') || str_contains($pPack, 'count') ||
            str_contains($pPack, 'pair') || str_contains($pPack, 'bottle') ||
            str_contains($pPack, 'ml') || str_contains($pPack, 'gm') || str_contains($pPack, 'syp') ||
            str_contains($pName, 'syp') || str_contains($pName, 'syrup') || str_contains($pName, 'drop') || str_contains($pName, 'ointment');
        return $isCount ? 'Nos' : 'Strips';
    }
}
