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

        $fromDate = $request->from_date ? Carbon::parse($request->from_date)->startOfDay() : now()->startOfMonth();
        $toDate = $request->to_date ? Carbon::parse($request->to_date)->endOfDay() : now()->endOfMonth();

        // 1. Prescription Trends Data
        $logs = PrescriptionLog::whereBetween('created_at', [$fromDate, $toDate])->get();
        $saltCounts = [];
        $totalMedicines = 0;
        $detailedMolecules = [];

        foreach ($logs as $log) {
            $data = $log->extracted_data;
            $medicines = $data['medicines'] ?? $data['line_items'] ?? $data['items'] ?? [];
            foreach ($medicines as $med) {
                $molecule = trim(strtoupper($med['generic_name'] ?? $med['salt'] ?? $med['name'] ?? null));
                if ($molecule && !in_array($molecule, ['N/A', 'UNKNOWN', '---', ''])) {
                    $saltCounts[$molecule] = ($saltCounts[$molecule] ?? 0) + 1;
                    $totalMedicines++;
                    $detailedMolecules[] = [
                        'name' => $molecule,
                        'retailer' => $log->retailer->name ?? 'N/A',
                        'date' => $log->created_at->format('d M Y'),
                        'confidence' => $med['confidence'] ?? 'N/A'
                    ];
                }
            }
        }
        arsort($saltCounts);
        $topSalts = array_slice($saltCounts, 0, 10);

        // 2. Sales Trend Data
        $salesTrends = Product::select('products.generic_name', DB::raw('SUM(retailer_order_items.quantity) as total_sold'))
            ->join('retailer_order_items', 'products.id', '=', 'retailer_order_items.product_id')
            ->join('retailer_orders', 'retailer_order_items.retailer_order_id', '=', 'retailer_orders.id')
            ->where('retailer_orders.status', 'delivered')
            ->whereNotNull('products.generic_name')
            ->whereNotIn(DB::raw('UPPER(products.generic_name)'), ['N/A', 'UNKNOWN', '---', ''])
            ->whereBetween('retailer_orders.placed_at', [$fromDate, $toDate])
            ->groupBy('products.generic_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        return view('admin.reports.molecule_analytics', compact(
            'fromDate', 'toDate', 'topSalts', 'detailedMolecules', 'salesTrends', 'totalMedicines'
        ));
    }
}
