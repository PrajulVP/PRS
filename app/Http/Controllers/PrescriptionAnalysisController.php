<?php

namespace App\Http\Controllers;

use App\Models\PrescriptionLog;
use App\Models\Product;
use App\Models\RetailerOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PrescriptionAnalysisController extends Controller
{
    /**
     * Display the Most Prescribed Salts report (from AI data).
     */
    public function prescribedSalts(Request $request)
    {
        abort_if(!Auth::user()->hasPermissionToCategory('reports', 'view') && !Auth::user()->hasRole(['superadmin', 'admin']), 403);
        
        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : now()->startOfMonth();
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : now()->endOfMonth();
        
        $query = PrescriptionLog::whereBetween('created_at', [$fromDate, $toDate]);
        
        if ($request->retailer_id) {
            $query->where('retailer_id', $request->retailer_id);
        }

        $logs = $query->get();
        $saltCounts = [];
        $totalMedicines = 0;

        foreach ($logs as $log) {
            $data = $log->extracted_data;
            // Flexible key check for various AI response formats
            $medicines = $data['medicines'] ?? $data['line_items'] ?? $data['items'] ?? [];
            
            foreach ($medicines as $med) {
                // Molecule extraction priority: generic_name -> salt -> name
                $molecule = $med['generic_name'] ?? $med['salt'] ?? $med['name'] ?? null;
                
                if ($molecule && !in_array(strtoupper($molecule), ['N/A', 'UNKNOWN', '---', ''])) {
                    $molecule = trim(strtoupper($molecule));
                    $saltCounts[$molecule] = ($saltCounts[$molecule] ?? 0) + 1;
                    $totalMedicines++;
                }
            }
        }

        arsort($saltCounts);
        $topSalts = array_slice($saltCounts, 0, 20); // Top 20

        // Detailed List for Table
        $detailedMolecules = [];
        foreach ($logs as $log) {
            $data = $log->extracted_data;
            $medicines = $data['medicines'] ?? $data['line_items'] ?? $data['items'] ?? [];
            foreach ($medicines as $med) {
                $detailedMolecules[] = [
                    'name' => trim(strtoupper($med['generic_name'] ?? $med['salt'] ?? $med['name'] ?? 'UNKNOWN')),
                    'retailer' => $log->retailer->name ?? 'N/A',
                    'date' => $log->created_at->format('d M Y H:i'),
                    'confidence' => $med['confidence'] ?? 'N/A'
                ];
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'labels' => array_keys($topSalts),
                'data' => array_values($topSalts),
                'total_prescriptions' => $logs->count(),
                'total_medicines' => $totalMedicines,
                'detailed' => $detailedMolecules
            ]);
        }

        return view('admin.reports.prescribed_salts', compact('topSalts', 'fromDate', 'toDate', 'detailedMolecules'));
    }

    /**
     * Display the Fastest Moving Molecules report (from Sales data).
     */
    public function fastestMovingMolecules(Request $request)
    {
        abort_if(!Auth::user()->hasPermissionToCategory('reports', 'view') && !Auth::user()->hasRole(['superadmin', 'admin']), 403);

        if ($request->ajax()) {
            $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : now()->startOfMonth();
            $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : now()->endOfMonth();

            $query = Product::select('products.generic_name', DB::raw('SUM(retailer_order_items.quantity) as total_sold'))
                ->join('retailer_order_items', 'products.id', '=', 'retailer_order_items.product_id')
                ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
                ->where('retailer_orders.status', 'delivered')
                ->whereNotNull('products.generic_name')
                ->whereNotIn(DB::raw('UPPER(products.generic_name)'), ['N/A', 'UNKNOWN', '---', ''])
                ->whereBetween('retailer_orders.placed_at', [$fromDate, $toDate])
                ->groupBy('products.generic_name')
                ->orderByDesc('total_sold');

            return DataTables::of($query)
                ->editColumn('total_sold', fn($row) => number_format($row->total_sold))
                ->make(true);
        }

        return view('admin.reports.fastest_moving_molecules');
    }

    public function moleculeAnalytics(Request $request)
    {
        abort_if(!Auth::user()->hasPermissionToCategory('reports', 'view') && !Auth::user()->hasRole(['superadmin', 'admin']), 403);

        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;

        $scope = $request->scope ?? 'portfolio';

        // 1. Prescription Trends Data
        $logsQuery = PrescriptionLog::query();
        if ($fromDate && $toDate) {
            $logsQuery->whereBetween('created_at', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $logsQuery->where('created_at', '>=', $fromDate);
        } elseif ($toDate) {
            $logsQuery->where('created_at', '<=', $toDate);
        }
        $logs = $logsQuery->get();

        $saltCounts = [];
        $totalMedicines = 0;
        $detailedMolecules = [];

        foreach ($logs as $log) {
            $data = $log->extracted_data;
            $medicines = $data['medicines'] ?? $data['line_items'] ?? $data['items'] ?? [];
            foreach ($medicines as $med) {
                $molecule = trim(strtoupper($med['generic_name'] ?? $med['salt'] ?? $med['name'] ?? null));
                if ($molecule && !in_array($molecule, ['N/A', 'UNKNOWN', '---', ''])) {
                    
                    // 1. Match product in database (using robust mapping)
                    $matchedProduct = \App\Models\Product::where('product_name', 'like', "%{$molecule}%")
                        ->orWhere('generic_name', 'like', "%{$molecule}%")
                        ->first();

                    if (!$matchedProduct) {
                        $words = explode(' ', preg_replace('/[^a-z0-9 ]/i', ' ', $molecule));
                        foreach ($words as $word) {
                            if (strlen($word) > 3) {
                                $matchedProduct = \App\Models\Product::where('product_name', 'like', "%{$word}%")
                                    ->orWhere('generic_name', 'like', "%{$word}%")
                                    ->first();
                                if ($matchedProduct) break;
                            }
                        }
                    }

                    // Dynamically filter based on Molecule Scope
                    if ($scope === 'portfolio' && !$matchedProduct) {
                        continue;
                    }

                    $saltCounts[$molecule] = ($saltCounts[$molecule] ?? 0) + 1;
                    $totalMedicines++;

                    $productName = null;
                    $brand = null;
                    $orderPlaced = false;

                    if ($matchedProduct) {
                        $productName = $matchedProduct->product_name;
                        $brand = $matchedProduct->brand ?: 'Standard';

                        // 2. Check if retailer placed an order containing this product on or after prescription upload
                        $orderPlaced = \App\Models\RetailerOrderItem::whereHas('retailerOrder', function($q) use ($log) {
                            $q->where('retailer_id', $log->retailer_id)
                              ->where('created_at', '>=', $log->created_at);
                        })->where('product_id', $matchedProduct->id)
                          ->exists();
                    }

                    $detailedMolecules[] = [
                        'name' => $molecule,
                        'retailer' => $log->retailer->shop_name ?? $log->retailer->user->name ?? 'N/A',
                        'date' => $log->created_at->format('d M Y'),
                        'confidence' => $med['confidence'] ?? 'N/A',
                        'product_name' => $productName,
                        'brand' => $brand,
                        'order_placed' => $orderPlaced
                    ];
                }
            }
        }
        arsort($saltCounts);
        $topSalts = array_slice($saltCounts, 0, 10);

        // 2. Sales Trend Data
        $salesTrendsQuery = Product::select('products.generic_name', DB::raw('SUM(retailer_order_items.quantity) as total_sold'))
            ->join('retailer_order_items', 'products.id', '=', 'retailer_order_items.product_id')
            ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
            ->where('retailer_orders.status', 'delivered')
            ->whereNotNull('products.generic_name')
            ->whereNotIn(DB::raw('UPPER(products.generic_name)'), ['N/A', 'UNKNOWN', '---', '']);
            
        if ($fromDate && $toDate) {
            $salesTrendsQuery->whereBetween('retailer_orders.placed_at', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $salesTrendsQuery->where('retailer_orders.placed_at', '>=', $fromDate);
        } elseif ($toDate) {
            $salesTrendsQuery->where('retailer_orders.placed_at', '<=', $toDate);
        }

        $salesTrends = $salesTrendsQuery->groupBy('products.generic_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return view('admin.reports.molecule_analytics', compact(
            'fromDate', 'toDate', 'topSalts', 'detailedMolecules', 'salesTrends', 'totalMedicines'
        ));
    }

    public function exportMoleculeAnalytics(Request $request)
    {
        abort_if(!Auth::user()->hasPermissionToCategory('reports', 'view') && !Auth::user()->hasRole(['superadmin', 'admin']), 403);

        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : null;
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : null;
        $scope = $request->scope ?? 'portfolio';

        // 1. Prescription Trends Data
        $logsQuery = PrescriptionLog::query();
        if ($fromDate && $toDate) {
            $logsQuery->whereBetween('created_at', [$fromDate, $toDate]);
        } elseif ($fromDate) {
            $logsQuery->where('created_at', '>=', $fromDate);
        } elseif ($toDate) {
            $logsQuery->where('created_at', '<=', $toDate);
        }
        $logs = $logsQuery->get();

        $detailedMolecules = [];

        foreach ($logs as $log) {
            $data = $log->extracted_data;
            $medicines = $data['medicines'] ?? $data['line_items'] ?? $data['items'] ?? [];
            foreach ($medicines as $med) {
                $molecule = trim(strtoupper($med['generic_name'] ?? $med['salt'] ?? $med['name'] ?? null));
                if ($molecule && !in_array($molecule, ['N/A', 'UNKNOWN', '---', ''])) {
                    
                    // 1. Match product in database (using robust mapping)
                    $matchedProduct = \App\Models\Product::where('product_name', 'like', "%{$molecule}%")
                        ->orWhere('generic_name', 'like', "%{$molecule}%")
                        ->first();

                    if (!$matchedProduct) {
                        $words = explode(' ', preg_replace('/[^a-z0-9 ]/i', ' ', $molecule));
                        foreach ($words as $word) {
                            if (strlen($word) > 3) {
                                $matchedProduct = \App\Models\Product::where('product_name', 'like', "%{$word}%")
                                    ->orWhere('generic_name', 'like', "%{$word}%")
                                    ->first();
                                if ($matchedProduct) break;
                            }
                        }
                    }

                    // Dynamically filter based on Molecule Scope
                    if ($scope === 'portfolio' && !$matchedProduct) {
                        continue;
                    }

                    $productName = $matchedProduct ? $matchedProduct->product_name : 'No SKU Match';
                    $brand = $matchedProduct ? ($matchedProduct->brand ?: 'Standard') : '-';
                    $orderPlaced = false;

                    if ($matchedProduct) {
                        // 2. Check if retailer placed an order containing this product on or after prescription upload
                        $orderPlaced = \App\Models\RetailerOrderItem::whereHas('retailerOrder', function($q) use ($log) {
                            $q->where('retailer_id', $log->retailer_id)
                              ->where('created_at', '>=', $log->created_at);
                        })->where('product_id', $matchedProduct->id)
                          ->exists();
                    }

                    $detailedMolecules[] = [
                        'name' => $molecule,
                        'product_name' => $productName,
                        'brand' => $brand,
                        'retailer' => $log->retailer->shop_name ?? $log->retailer->user->name ?? 'N/A',
                        'date' => $log->created_at->format('Y-m-d H:i'),
                        'confidence' => isset($med['confidence']) ? $med['confidence'] . '%' : 'N/A',
                        'order_placed' => $orderPlaced ? 'Yes' : 'No'
                    ];
                }
            }
        }

        $filename = "molecule_" . ($scope === 'portfolio' ? 'portfolio' : 'extracted') . "_analytics_" . now()->format('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($detailedMolecules, $fromDate, $toDate, $scope) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ["PRESCRIPTION MOLECULE ANALYTICS REPORT"]);
            fputcsv($file, []); // Spacer
            
            fputcsv($file, ["REPORT PARAMETERS:"]);
            fputcsv($file, [" - Date Range: " . ($fromDate ? $fromDate->format('Y-m-d') : 'All Time') . " to " . ($toDate ? $toDate->format('Y-m-d') : 'All Time')]);
            fputcsv($file, [" - Molecule Scope: " . ($scope === 'portfolio' ? 'Company Portfolio Only' : 'All Extracted Molecules')]);
            fputcsv($file, []); // Spacer

            fputcsv($file, ['Molecule Name', 'Matched Product', 'Brand', 'Retailer Source', 'Capture Date', 'Order Conversion', 'AI Confidence']);

            foreach ($detailedMolecules as $row) {
                fputcsv($file, [
                    $row['name'],
                    $row['product_name'],
                    $row['brand'],
                    $row['retailer'],
                    $row['date'],
                    $row['order_placed'],
                    $row['confidence']
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
