<?php

namespace App\Http\Controllers;

use App\Services\OcrService;
use App\Models\Product;
use App\Models\Retailer;
use App\Models\Distributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrescriptionController extends Controller
{
    protected $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Extract medicines from a prescription and match them with system products.
     */
    public function extract(Request $request)
    {
        $request->validate([
            'prescription' => 'required|file|mimes:jpg,jpeg,png,pdf',
            'retailer_id' => 'required|exists:retailers,id'
        ]);

        $file = $request->file('prescription');
        $retailer = Retailer::find($request->retailer_id);

        $extractedData = $this->ocrService->extractPrescription($file);

        // Flexible key check
        $ocrItems = $extractedData['medicines'] ?? $extractedData['line_items'] ?? $extractedData['items'] ?? null;

        if (!$extractedData || is_null($ocrItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to extract medicines from the prescription.'
            ], 500);
        }

        $matchedProducts = [];

        foreach ($ocrItems as $medicine) {
            $name = $medicine['name'] ?? $medicine['product_name'] ?? $medicine['description'] ?? null;
            $quantity = $medicine['count'] ?? $medicine['quantity'] ?? $medicine['qty'] ?? 1;

            if (!$name) {
                Log::warning('OCR medicine missing name', ['medicine' => $medicine]);
                continue;
            }

            // Search for product with fuzzy matching or exact name match
            $product = Product::where('product_name', 'LIKE', "%{$name}%")->first();

            // Fallback: If "Mox 500" came from OCR, try searching for just "Mox" or parts of it
            if (!$product) {
                $parts = explode(' ', $name);
                if (count($parts) > 0) {
                    $firstPart = $parts[0];
                    if (strlen($firstPart) > 2) {
                        $product = Product::where('product_name', 'LIKE', "%{$firstPart}%")->first();
                    }
                }
            }

            if ($product) {
                // Get available distributors and calculate distance, similar to getProductDetails
                $distributors = $this->getAvailableDistributors($product, $retailer);

                if ($distributors->isNotEmpty()) {
                    $bestDistributor = $distributors->first();

                    $matchedProducts[] = [
                        'product' => $product,
                        'distributor' => $bestDistributor,
                        'quantity' => $quantity,
                        'unit' => $this->determineDefaultUnit($product),
                        'source' => 'ocr'
                    ];
                    Log::info('OCR Match Success', ['name' => $name, 'matched' => $product->product_name]);
                } else {
                    Log::warning('OCR Match Failed: No Distributor with Stock', ['product' => $product->product_name]);
                }
            } else {
                Log::warning('OCR Match Failed: Product Not Found', ['name' => $name]);
            }
        }

        return response()->json([
            'success' => true,
            'medicines' => $matchedProducts,
            'data' => $extractedData // Added for debugging in network tab
        ]);
    }

    /**
     * Shared logic to get distributors for a product, sorted by distance and stock.
     */
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
                    (float)$retailer->latitude,
                    (float)$retailer->longitude,
                    (float)$distributor->latitude,
                    (float)$distributor->longitude
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
