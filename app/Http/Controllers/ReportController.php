<?php

namespace App\Http\Controllers;

use App\Models\Distributor;
use App\Models\Retailer;
use App\Models\FieldStaff;
use App\Models\Product;
use App\Models\RetailerOrder;
use App\Models\RetailerOrderItem;
use App\Models\DistributorOrder;
use App\Models\SalesManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Default Date Range: Current Month
        $fromDate = now()->startOfMonth();
        $toDate = now()->endOfMonth();

        // Basic summary stats for the dashboard
        $stats = [
            'total_retailer_orders' => 0,
            'total_distributor_orders' => 0,
            'total_sales_value' => 0,
            'active_retailers' => 0,
            'pending_payments' => 0,
            'total_visits' => 0,
            'prescriptions_analyzed' => 0,
        ];

        $query = RetailerOrder::query()->whereBetween('placed_at', [$fromDate, $toDate]);
        $distQuery = DistributorOrder::query()->whereBetween('created_at', [$fromDate, $toDate]);

        if ($user->hasRole('fieldstaff')) {
            $query->where('fieldstaff_id', $user->fieldStaff->id);
            $stats['active_retailers'] = Retailer::where('field_staff_id', $user->fieldStaff->id)->count();
            $stats['total_visits'] = \App\Models\VisitLog::where('user_id', $user->id)->whereBetween('check_in_at', [$fromDate, $toDate])->count();
        } elseif ($user->hasRole('salesmanager')) {
            $query->whereHas('fieldStaff', function($q) use ($user) {
                $q->where('sales_manager_id', $user->salesManager->id);
            });
            $stats['active_retailers'] = Retailer::whereHas('fieldStaff', function($q) use ($user) {
                $q->where('sales_manager_id', $user->salesManager->id);
            })->count();
            $stats['total_visits'] = \App\Models\VisitLog::whereHas('user.fieldStaff', function($q) use ($user) {
                $q->where('sales_manager_id', $user->salesManager->id);
            })->whereBetween('check_in_at', [$fromDate, $toDate])->count();
        } elseif ($user->hasRole('distributor')) {
            $distributor = $user->distributor;
            $query->where('distributor_id', $distributor->id);
            $distQuery->where('distributor_id', $distributor->id);
            
            $stats['active_retailers'] = RetailerOrder::where('distributor_id', $distributor->id)
                ->distinct('retailer_id')
                ->count();
            
            $stats['total_visits'] = \App\Models\VisitLog::whereHas('retailer', function($q) use ($distributor) {
                $q->where('district_id', $distributor->district_id);
            })->whereBetween('check_in_at', [$fromDate, $toDate])->count();
        } else {
            $stats['active_retailers'] = Retailer::count();
            $stats['total_visits'] = \App\Models\VisitLog::whereBetween('check_in_at', [$fromDate, $toDate])->count();
        }

        $stats['total_retailer_orders'] = (clone $query)->count();
        $stats['total_sales_value'] = (clone $query)->where('status', RetailerOrder::STATUS_DELIVERED)->sum('total_amount');
        $stats['total_distributor_orders'] = $distQuery->count();
        
        $stats['pending_payments'] = (clone $query)->where('payment_status', '!=', 'paid')->sum('total_amount') + 
                                     (clone $distQuery)->where('payment_status', '!=', 'paid')->sum('total_amount');

        // Prescription Logs Count (Scoped)
        $pQuery = \App\Models\PrescriptionLog::whereBetween('created_at', [$fromDate, $toDate]);
        if ($user->hasRole('distributor')) {
            $pQuery->whereHas('retailer', function($q) use ($user) {
                $q->where('distributor_id', $user->distributor->id);
            });
        } elseif ($user->hasRole('salesmanager')) {
            $pQuery->whereHas('retailer.fieldStaff', function($q) use ($user) {
                $q->where('sales_manager_id', $user->salesManager->id);
            });
        } elseif ($user->hasRole('fieldstaff')) {
            $pQuery->whereHas('retailer', function($q) use ($user) {
                $q->where('field_staff_id', $user->fieldStaff->id);
            });
        }
        $stats['prescriptions_analyzed'] = $pQuery->count();

        return view('admin.reports.index', compact('stats', 'fromDate', 'toDate'));
    }

    protected function getFilterDates(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $period = $request->period;

        if ($fromDate && $toDate) {
            return [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay()
            ];
        }

        if ($period && $period !== 'all') {
            switch ($period) {
                case 'today':
                    return [now()->startOfDay(), now()->endOfDay()];
                case 'yesterday':
                    return [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()];
                case '7days':
                    return [now()->subDays(7)->startOfDay(), now()->endOfDay()];
                case 'this_month':
                    return [now()->startOfMonth(), now()->endOfMonth()];
                case 'this_year':
                    return [now()->startOfYear(), now()->endOfDay()];
                default:
                    // Fallback for YYYY-MM period format if still used
                    try {
                        $date = Carbon::parse($period);
                        return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
                    } catch (\Exception $e) {
                        return [null, null];
                    }
            }
        }

        return [null, null];
    }

    protected function applyGlobalFilters($query, Request $request, $dateColumn = null)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        $model = $query->getModel();
        $tableName = $model->getTable();

        // Specific Manager Filter
        $managerId = $request->sales_manager_id;
        
        // Scope by Authenticated User Role
        if ($user->hasRole('fieldstaff')) {
            if ($model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'field_staff_id')) {
                $query->where('field_staff_id', $user->fieldStaff->id);
            } elseif ($model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'fieldstaff_id')) {
                $query->where('fieldstaff_id', $user->fieldStaff->id);
            }
        } elseif ($user->hasRole('salesmanager')) {
            $managerId = $user->salesManager->id;
        } elseif ($user->hasRole('distributor')) {
            $distId = $user->distributor->id;
            if ($model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'distributor_id')) {
                $query->where($tableName . '.distributor_id', $distId);
            } elseif ($tableName === 'retailers') {
                $query->whereHas('orders', function($q) use ($distId) {
                    $q->where('distributor_id', $distId);
                });
            } elseif ($tableName === 'fieldstaffs') {
                $query->whereHas('retailerOrders', function($q) use ($distId) {
                    $q->where('distributor_id', $distId);
                });
            } elseif ($tableName === 'visit_logs') {
                $query->whereHas('retailer', function($q) use ($distId) {
                    $q->where('distributor_id', $distId);
                });
            }
        }

        if ($managerId) {
            if ($model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'sales_manager_id')) {
                $query->where('sales_manager_id', $managerId);
            } elseif (method_exists($model, 'fieldStaff') || method_exists($model, 'retailer')) {
                // If it belongs to retailer or fieldstaff which belongs to sales manager
                $query->whereHas('fieldStaff', function($q) use ($managerId) {
                    $q->where('sales_manager_id', $managerId);
                });
            }
        }

        if ($request->sales_manager_id) { 
            if ($model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'sales_manager_id')) {
                $query->where('sales_manager_id', $request->sales_manager_id); 
            } elseif (method_exists($model, 'fieldStaff')) {
                $query->whereHas('fieldStaff', function($q) use ($request) {
                    $q->where('sales_manager_id', $request->sales_manager_id);
                });
            }
        }
        
        if ($request->fieldstaff_id && $model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'field_staff_id')) { 
            $query->where('field_staff_id', $request->fieldstaff_id); 
        } elseif ($request->fieldstaff_id && $model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'fieldstaff_id')) { 
            $query->where('fieldstaff_id', $request->fieldstaff_id); 
        }

        if ($request->retailer_id && $model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'retailer_id')) { 
            $query->where('retailer_id', $request->retailer_id); 
        }
        
        if (!$user->hasRole('distributor') && $request->distributor_id && $model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'distributor_id')) { 
            $query->where('distributor_id', $request->distributor_id); 
        }
        
        if ($request->status && $model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'status')) { 
            $query->where('status', $request->status); 
        }
        
        if ($request->payment_status && $model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'payment_status')) { 
            $query->where('payment_status', $request->payment_status); 
        }

        if ($request->brand) {
            if ($model->getConnection()->getSchemaBuilder()->hasColumn($tableName, 'brand')) {
                $query->where('brand', $request->brand);
            } elseif (method_exists($model, 'product')) {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('brand', $request->brand);
                });
            } elseif (method_exists($model, 'items')) {
                $query->whereHas('items.product', function($q) use ($request) {
                    $q->where('brand', $request->brand);
                });
            } elseif ($tableName === 'retailer_order_items' || $tableName === 'distributor_order_items') {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('brand', $request->brand);
                });
            }
        }

        // Date Range (Only if dateColumn is provided and exists)
        if ($dateColumn && $model->getConnection()->getSchemaBuilder()->hasColumn($tableName, $dateColumn)) {
            [$f, $t] = $this->getFilterDates($request);
            if ($f && $t) {
                $query->whereBetween($dateColumn, [$f, $t]);
            }
        }

        return $query;
    }

    public function getStaffByManager(Request $request)
    {
        $managerId = $request->sales_manager_id;
        if (!$managerId) return response()->json([]);
 
        $staff = FieldStaff::where('sales_manager_id', $managerId)->with('user')->get();
        return response()->json($staff->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->user->name ?? 'Staff #' . $s->id
        ]));
    }
 
    /**
     * Security Helper: Determine if the current user should see sensitive commercial data
     */
    protected function isManagement()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        return $user->hasRole('admin') || $user->hasRole('salesmanager');
    }

    public function orderReports(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('master_order_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        if ($request->ajax()) {
            $type = $request->order_type ?? 'retailer';
            
            if ($type === 'distributor') {
                $query = DistributorOrder::with(['distributor.user', 'salesManager.user', 'items.product'])
                    ->select('distributor_orders.*');
            } else {
                $query = RetailerOrder::with(['retailer.user', 'distributor.user', 'fieldStaff.user', 'items.product'])
                    ->select('retailer_orders.*');
            }

            $dateCol = ($type === 'distributor') ? 'created_at' : 'placed_at';
            $this->applyGlobalFilters($query, $request, $dateCol);

            return DataTables::of($query)
                ->addColumn('retailer_name', function($row) use ($type) {
                    return ($type === 'retailer') ? ($row->retailer->user->name ?? 'N/A') : 'N/A';
                })
                ->addColumn('distributor_name', fn($row) => $row->distributor->user->name ?? 'N/A')
                ->addColumn('fieldstaff_name', function($row) use ($type) {
                    return ($type === 'retailer') ? ($row->fieldStaff->user->name ?? 'N/A') : 'N/A';
                })
                ->addColumn('items_detail', function($row) {
                    return $row->items->groupBy(function($item) {
                        return $item->product_id . '-' . ($item->side ?? '') . '-' . ($item->size ?? '');
                    })->map(function($group) {
                        $first = $group->first();
                        $pName = $first->product->product_name ?? 'Product';
                        $qty = $group->sum('quantity');
                        $free = $group->sum('free_quantity');
                        $freeTxt = $free > 0 ? " + {$free} Free" : "";
                        $variant = array_filter([$first->side, $first->size]);
                        $vTxt = !empty($variant) ? " [" . implode('/', $variant) . "]" : "";
                        return "{$pName}{$vTxt} (x{$qty}{$freeTxt})";
                    })->implode(', ');
                })
                ->addColumn('total_quantity', fn($row) => $row->items->sum('quantity'))
                ->addColumn('total_items', fn($row) => $row->items->count())
                ->editColumn('placed_at', fn($row) => $row->placed_at->format('M d, Y H:i'))
                ->editColumn('total_amount', fn($row) => number_format($row->total_amount, 2))
                ->addColumn('tax_summary', function($row) {
                    if (!$this->isManagement()) return '***';
                    $items = $row->items->load('product');
                    $tax = $items->sum(fn($i) => ($i->product->gst / 100) * ($i->product->taxable_value * $i->quantity));
                    return '₹' . number_format($tax, 2);
                })
                ->rawColumns(['retailer_name', 'total_quantity', 'tax_summary', 'status'])
                ->make(true);
        }

        $distributors = Distributor::with('user')->get();
        $retailers = Retailer::with('user')->get();
        $fieldStaffs = FieldStaff::with('user')->get();
        $salesManagers = SalesManager::with('user')->get();
        $availableBrands = array_filter(array_map('trim', explode(',', \App\Models\Setting::getValue('product_brands') ?? '')));
 
        return view('admin.reports.orders', compact('distributors', 'retailers', 'fieldStaffs', 'salesManagers', 'availableBrands'));
    }

    public function distributorReports(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('distributor_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        if ($request->ajax()) {
            $query = Distributor::with('user')->select('distributors.*');
            
            $type = $request->order_type ?? 'retailer';
            $rel = ($type === 'distributor') ? 'distributorOrders' : 'retailerOrders';
            
            // Only filter the Distributor LIST by hierarchy if specifically requested
            // We ignore dates for the existence check to satisfy "once placed order"
            if ($request->sales_manager_id || $request->fieldstaff_id || $request->retailer_id) {
                $query->whereHas($rel, function($q) use ($request) {
                    $this->applyGlobalFilters($q, $request, null); // Ignore dates for list membership
                });
            }

            $query->withCount([$rel . ' as total_orders' => function($q) use ($request) {
                $this->applyGlobalFilters($q, $request);
            }])
            ->withSum([$rel . ' as total_sales' => function($q) use ($request) {
                $this->applyGlobalFilters($q, $request);
                $q->where('status', 'delivered');
            }], 'total_amount');

            return DataTables::of($query)
                ->addColumn('name', fn($dist) => $dist->user->name ?? $dist->name)
                ->addColumn('contact', fn($dist) => $dist->user->phone ?? 'N/A')
                ->addColumn('network_size', function($dist) use ($rel) {
                    return $dist->$rel()->distinct('retailer_id')->count() . ' Retailers';
                })
                ->addColumn('top_products', function($dist) use ($request) {
                    $type = $request->order_type ?? 'retailer';
                    $orderItemModel = ($type === 'distributor') ? \App\Models\DistributorOrderItem::class : \App\Models\RetailerOrderItem::class;
                    $orderRel = ($type === 'distributor') ? 'distributorOrder' : 'retailerOrder';

                    $top = $orderItemModel::whereHas($orderRel, function($q) use ($dist, $request) {
                        $q->where('distributor_id', $dist->id);
                        $this->applyGlobalFilters($q, $request);
                        $q->where('status', 'delivered');
                    })
                    ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
                    ->groupBy('product_id')
                    ->orderByDesc('total_qty')
                    ->limit(1)
                    ->with('product')
                    ->get();

                    if ($top->isEmpty()) return '<span class="text-muted small italic">N/A</span>';
                    
                    return $top->map(function($item) {
                        $name = $item->product->product_name ?? 'Unknown';
                        return "<div class='x-small'><i class='fa fa-caret-right me-1 text-primary'></i>{$name}</div>";
                    })->implode('');
                })
                ->rawColumns(['total_sales', 'top_products'])
                ->make(true);
        }

        $salesManagers = SalesManager::with('user')->get();
        return view('admin.reports.distributors', compact('salesManagers'));
    }

    public function retailerReports(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('retailer_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        if ($request->ajax()) {
            $query = Retailer::with(['user', 'fieldStaff.user'])->select('retailers.*');

            // Apply global hierarchy filters to the main query
            $this->applyGlobalFilters($query, $request, null);

            $query->withCount(['orders as total_orders' => function($q) use ($request) {
                $this->applyGlobalFilters($q, $request, 'placed_at');
            }])
            ->withSum(['orders as total_sales' => function($q) use ($request) {
                $this->applyGlobalFilters($q, $request, 'placed_at');
                $q->where('status', RetailerOrder::STATUS_DELIVERED);
            }], 'total_amount');

            return DataTables::of($query)
                ->addColumn('name', fn($ret) => $ret->user->name ?? 'N/A')
                ->addColumn('shop_details', function($ret) {
                    $location = $ret->area->name ?? 'N/A';
                    return "<div class='fw-bold'>{$ret->shop_name}</div><div class='small text-muted'>{$location}</div>";
                })
                ->addColumn('regulatory', function($ret) {
                    if (!$this->isManagement()) return 'Restricted';
                    return "GST: " . ($ret->gst ?: 'N/A') . "<br>DL: " . ($ret->drug_license_no ?: 'N/A');
                })
                ->addColumn('field_staff', fn($ret) => $ret->fieldStaff->user->name ?? 'N/A')
                ->addColumn('top_products', function($ret) use ($request) {
                    $top = \App\Models\RetailerOrderItem::whereHas('retailerOrder', function($q) use ($ret, $request) {
                        $q->where('retailer_id', $ret->id);
                        $this->applyGlobalFilters($q, $request, 'placed_at');
                        $q->where('status', 'delivered');
                    })
                    ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
                    ->groupBy('product_id')
                    ->orderByDesc('total_qty')
                    ->limit(1)
                    ->with('product')
                    ->get();
                    
                    if ($top->isEmpty()) return '<span class="text-muted small italic">N/A</span>';
                    
                    return $top->map(function($item) {
                        $name = $item->product->product_name ?? 'Unknown';
                        return "<div class='x-small'><i class='fa fa-caret-right me-1 text-success'></i>{$name}</div>";
                    })->implode('');
                })
                ->rawColumns(['shop_details', 'regulatory', 'top_products'])
                ->make(true);
        }

        $distributors = Distributor::with('user')->get();
        $fieldStaffs = FieldStaff::with('user')->get();
        $salesManagers = SalesManager::with('user')->get();

        return view('admin.reports.retailers', compact('distributors', 'fieldStaffs', 'salesManagers'));
    }

    public function productReports(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('product_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        if ($request->ajax()) {
            $type = $request->order_type ?? 'retailer';
            $orderModel = ($type === 'distributor') ? \App\Models\DistributorOrder::class : \App\Models\RetailerOrder::class;
            $orderItemModel = ($type === 'distributor') ? \App\Models\DistributorOrderItem::class : \App\Models\RetailerOrderItem::class;
            $orderIdCol = ($type === 'distributor') ? 'distributor_order_id' : 'retailer_order_id';
            $orderRel = ($type === 'distributor') ? 'distributorOrder' : 'retailerOrder';
            $totalAmountCol = ($type === 'distributor') ? 'subtotal' : 'total_amount';
            $deliveredStatus = ($type === 'distributor') ? \App\Models\DistributorOrder::STATUS_DELIVERED : \App\Models\RetailerOrder::STATUS_DELIVERED;
            $dateCol = ($type === 'distributor') ? 'created_at' : 'placed_at';

            $query = Product::select('products.*')
                ->selectSub(
                    $orderItemModel::whereColumn('product_id', 'products.id')
                        ->whereHas($orderRel, function($q) use ($request, $dateCol) {
                            $this->applyGlobalFilters($q, $request, $dateCol);
                            $q->where('status', 'delivered'); 
                        })->selectRaw('COALESCE(SUM(quantity), 0)'), 
                    'total_sold'
                )
                ->selectSub(
                    $orderItemModel::whereColumn('product_id', 'products.id')
                        ->whereHas($orderRel, function($q) use ($request, $dateCol) {
                            $this->applyGlobalFilters($q, $request, $dateCol);
                            $q->where('status', 'delivered');
                        })->selectRaw('COALESCE(SUM(free_quantity), 0)'), 
                    'total_free'
                )
                ->selectSub(
                    $orderItemModel::whereColumn('product_id', 'products.id')
                        ->whereHas($orderRel, function($q) use ($request, $dateCol) {
                            $this->applyGlobalFilters($q, $request, $dateCol);
                            $q->where('status', 'delivered');
                        })->selectRaw("COALESCE(SUM({$totalAmountCol}), 0)"), 
                    'total_revenue'
                )
                ->selectSub(
                    $orderItemModel::whereColumn('product_id', 'products.id')
                        ->whereHas($orderRel, function($q) use ($request, $dateCol) {
                            $this->applyGlobalFilters($q, $request, $dateCol);
                            $q->where('status', 'delivered');
                        })->selectRaw("COALESCE(COUNT(DISTINCT {$orderIdCol}), 0)"), 
                    'order_count'
                );

            if ($request->brand) {
                $query->where('brand', $request->brand);
            }

            return DataTables::of($query)
                ->addColumn('brand_display', fn($prod) => $prod->brand ?: '<span class="text-muted small italic">Standard</span>')
                ->addColumn('pricing', function($prod) {
                    if (!$this->isManagement()) return '***';
                    return "PTR: ₹" . number_format($prod->ptr, 2) . "<br>MRP: ₹" . number_format($prod->mrp, 2);
                })
                ->addColumn('avg_units', function($prod) {
                    if (!$prod->order_count) return 0;
                    return number_format($prod->total_sold / $prod->order_count, 1);
                })
                ->editColumn('total_revenue', fn($prod) => number_format($prod->total_revenue ?? 0, 2))
                ->editColumn('total_sold', fn($prod) => number_format($prod->total_sold ?? 0))
                ->rawColumns(['pricing', 'brand_display'])
                ->make(true);
        }

        $salesManagers = SalesManager::with('user')->get();
        $distributors = Distributor::with('user')->get();
        $fieldStaffs = FieldStaff::with('user')->get();
        $availableBrands = array_filter(array_map('trim', explode(',', \App\Models\Setting::getValue('product_brands') ?? '')));

        return view('admin.reports.products', compact('salesManagers', 'distributors', 'fieldStaffs', 'availableBrands'));
    }

    public function brandReports(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('product_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        if ($request->ajax()) {
            $type = $request->order_type ?? 'retailer';
            $orderItemModel = ($type === 'distributor') ? \App\Models\DistributorOrderItem::class : \App\Models\RetailerOrderItem::class;
            $orderRel = ($type === 'distributor') ? 'distributorOrder' : 'retailerOrder';
            $totalAmountCol = ($type === 'distributor') ? 'subtotal' : 'total_amount';

            $brands = array_filter(array_map('trim', explode(',', \App\Models\Setting::getValue('product_brands') ?? '')));
            
            $data = [];
            foreach ($brands as $brand) {
                $query = $orderItemModel::whereHas('product', function($q) use ($brand) {
                    $q->where('brand', $brand);
                })->whereHas($orderRel, function($q) use ($request, $type) {
                    $dateCol = ($type === 'distributor') ? 'created_at' : 'placed_at';
                    $this->applyGlobalFilters($q, $request, $dateCol);
                    $q->where('status', 'delivered');
                });

                $stats = $query->selectRaw("
                    COUNT(DISTINCT product_id) as unique_products,
                    SUM(quantity) as total_sold,
                    SUM({$totalAmountCol}) as total_revenue
                ")->first();

                if ($stats->total_sold > 0) {
                    $data[] = [
                        'brand' => $brand,
                        'unique_products' => $stats->unique_products,
                        'total_sold' => $stats->total_sold,
                        'total_revenue' => $stats->total_revenue
                    ];
                }
            }

            return DataTables::of(collect($data))
                ->editColumn('total_revenue', fn($row) => number_format($row['total_revenue'], 2))
                ->make(true);
        }

        $salesManagers = SalesManager::with('user')->get();
        $availableBrands = array_filter(array_map('trim', explode(',', \App\Models\Setting::getValue('product_brands') ?? '')));

        return view('admin.reports.brands', compact('salesManagers', 'availableBrands'));
    }

    public function fieldStaffReports(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('performance_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->ajax()) {
            $today = now()->toDateString();
            $query = FieldStaff::with(['user', 'salesManager.user'])
                ->selectRaw("fieldstaffs.*, (SELECT CASE WHEN type = 'punch_in' THEN 1 ELSE 0 END FROM attendance_logs WHERE attendance_logs.user_id = fieldstaffs.user_id AND DATE(attendance_logs.timestamp) = ? ORDER BY timestamp DESC LIMIT 1) as is_online", [$today]);
            [$f, $t] = $this->getFilterDates($request);

            // Scoping
            if ($user->hasRole('salesmanager')) {
                $query->where('sales_manager_id', $user->salesManager->id);
            } elseif ($request->sales_manager_id) {
                $query->where('sales_manager_id', $request->sales_manager_id);
            }

            $type = $request->order_type ?? 'retailer';

            // Relationship Counts & Sums (Enhanced)
            $query->withCount(['retailers as total_retailers'])
                ->withCount(['retailerOrders as total_orders' => function($q) use ($f, $t) {
                    if ($f && $t) $q->whereBetween('placed_at', [$f, $t]);
                }])
                ->withSum(['retailerOrders as total_revenue' => function($q) use ($f, $t) {
                    $q->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED);
                    if ($f && $t) $q->whereBetween('placed_at', [$f, $t]);
                }], 'total_amount')
                ->withCount(['visitLogs as total_visits' => function($q) use ($f, $t) {
                    if ($f && $t) $q->whereBetween('created_at', [$f, $t]);
                }])
                ->withCount(['attendanceLogs as total_punches' => function($q) use ($f, $t) {
                    if ($f && $t) $q->whereBetween('timestamp', [$f, $t]);
                }]);

            return DataTables::of($query)
                ->addColumn('name', function($fs) {
                    $status = $fs->is_online ? '<span class="live-dot" style="width:8px;height:8px;background:#2ecc71;border-radius:50%;display:inline-block;margin-right:5px;"></span>' : '<span style="width:8px;height:8px;background:#95a5a6;border-radius:50%;display:inline-block;margin-right:5px;"></span>';
                    return $status . ($fs->user->name ?? 'N/A');
                })
                ->addColumn('manager', fn($fs) => $fs->salesManager->user->name ?? 'N/A')
                ->addColumn('location', function($fs) {
                    $district = $fs->district->name ?? $fs->user->city ?? 'N/A';
                    $area = $fs->area->name ?? $fs->user->address ?? 'N/A';
                    return "<div class='fw-bold'>{$district}</div><div class='small text-muted'>{$area}</div>";
                })
                ->addColumn('coverage_stats', function($fs) {
                    return "<div class='fw-bold'>{$fs->total_retailers} Outlets</div><div class='small text-muted'>{$fs->total_visits} Visits Logged</div>";
                })
                ->addColumn('activity', function($fs) {
                    $punches = $fs->total_punches ?? 0;
                    return "<div class='badge badge-light-primary'>{$punches} Punches</div>";
                })
                ->addColumn('aov', function($fs) {
                    if (!$fs->total_orders) return '₹0.00';
                    return '₹' . number_format($fs->total_revenue / $fs->total_orders, 2);
                })
                ->addColumn('actions', function($fs) {
                    return '<a href="' . route('admin.reports.fieldstaff.tracking', ['user_id' => $fs->user_id]) . '" class="btn btn-sm btn-primary"><i class="fa fa-map-marker-alt me-1"></i>Track</a>';
                })
                ->editColumn('total_revenue', fn($fs) => '₹' . number_format($fs->total_revenue ?? 0, 2))
                ->rawColumns(['name', 'location', 'coverage_stats', 'activity', 'actions'])
                ->make(true);
        }

        $salesManagers = SalesManager::with('user')->get();
        $distributors = Distributor::with('user')->get();
        $fieldStaffs = FieldStaff::with('user')->get();

        $today = now()->toDateString();
        $activeStaffCount = \App\Models\AttendanceLog::whereDate('timestamp', $today)
            ->where('type', 'punch_in')
            ->distinct('user_id')
            ->count();

        $todayVisitsCount = \App\Models\VisitLog::whereDate('check_in_at', $today)->count();
        
        $pulseStats = [
            'active' => $activeStaffCount,
            'visits' => $todayVisitsCount,
            'alerts' => 0,
        ];

        return view('admin.reports.fieldstaffs', compact('salesManagers', 'distributors', 'fieldStaffs', 'pulseStats'));
    }

    public function visitReports(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('performance_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        if ($request->ajax()) {
            $query = FieldStaff::with(['user', 'salesManager.user'])->select('fieldstaffs.*');
            $this->applyGlobalFilters($query, $request);

            [$f, $t] = $this->getFilterDates($request);

            $query->withCount(['visitLogs as total_visits' => function($q) use ($f, $t) {
                if ($f && $t) $q->whereBetween('check_in_at', [$f, $t]);
            }])
            ->withCount(['retailers as total_assigned_retailers']);

            return DataTables::of($query)
                ->addColumn('name', fn($fs) => $fs->user->name ?? 'N/A')
                ->addColumn('coverage', function($fs) {
                    $visitedUnique = \App\Models\VisitLog::where('user_id', $fs->user_id)
                        ->where('customer_category', 'retailer')
                        ->count(\DB::raw('DISTINCT customer_id'));
                        
                    return "{$visitedUnique} / {$fs->total_assigned_retailers} Outlets";
                })
                ->addColumn('productivity', function($fs) {
                    if ($fs->total_assigned_retailers == 0) return '0%';
                    // Use customer_id and filter by category if necessary
                    $visitedUnique = \App\Models\VisitLog::where('user_id', $fs->user_id)
                        ->where('customer_category', 'retailer')
                        ->count(\DB::raw('DISTINCT customer_id'));
                    $percent = ($visitedUnique / $fs->total_assigned_retailers) * 100;
                    return number_format($percent, 1) . '% Coverage';
                })
                ->addColumn('visit_count', fn($fs) => $fs->total_visits . ' Visits')
                ->make(true);
        }

        $salesManagers = SalesManager::with('user')->get();
        return view('admin.reports.visits', compact('salesManagers'));
    }

    public function outstandingReports(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('retailer_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        if ($request->ajax()) {
            $type = $request->order_type ?? 'retailer';
            
            if ($type === 'distributor') {
                $query = Distributor::with('user')->select('distributors.*');
                $rel = 'distributorOrders';
            } else {
                $query = Retailer::with(['user', 'fieldStaff.user'])->select('retailers.*');
                $rel = 'orders';
            }

            $this->applyGlobalFilters($query, $request, null);

            // Total Business
            $query->withSum([$rel . ' as total_business'], 'total_amount');

            // Outstanding (Unpaid)
            $query->withSum([$rel . ' as total_outstanding' => function($q) {
                $q->where('payment_status', '!=', 'paid');
                $q->where('status', '!=', 'cancelled');
                $q->where('status', '!=', 'rejected');
            }], 'total_amount');

            return DataTables::of($query)
                ->addColumn('entity_name', function($row) use ($type) {
                    if ($type === 'distributor') return $row->user->name ?? $row->name;
                    return "<div class='fw-bold'>{$row->shop_name}</div><div class='small text-muted'>{$row->user->name}</div>";
                })
                ->addColumn('business', fn($row) => '₹' . number_format($row->total_business ?? 0, 2))
                ->addColumn('outstanding', fn($row) => '₹' . number_format($row->total_outstanding ?? 0, 2))
                ->addColumn('risk_level', function($row) {
                    $outstanding = $row->total_outstanding ?? 0;
                    if ($outstanding == 0) return '<span class="badge bg-light-success text-success">Clear</span>';
                    if ($outstanding > 50000) return '<span class="badge bg-light-danger text-danger">Critical</span>';
                    return '<span class="badge bg-light-warning text-warning">Active</span>';
                })
                ->rawColumns(['entity_name', 'risk_level'])
                ->make(true);
        }

        $salesManagers = SalesManager::with('user')->get();
        return view('admin.reports.outstanding', compact('salesManagers'));
    }

    public function targetReports(Request $request)
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('performance_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        
        if ($request->ajax()) {

            $query = FieldStaff::with(['user', 'salesManager.user'])->select('fieldstaffs.*');
            $this->applyGlobalFilters($query, $request);

            [$f, $t] = $this->getFilterDates($request);

            $query->withSum(['retailerOrders as achievement' => function($q) use ($f, $t) {
                $q->where('status', \App\Models\RetailerOrder::STATUS_DELIVERED);
                if ($f && $t) $q->whereBetween('placed_at', [$f, $t]);
            }], 'total_amount');

            $query->withSum(['salesTargets as target_amount'], 'amount');

            return DataTables::of($query)
                ->addColumn('name', fn($fs) => $fs->user->name ?? 'N/A')
                ->addColumn('achievement_display', fn($fs) => '₹' . number_format($fs->achievement ?? 0, 2))
                ->addColumn('target_display', fn($fs) => '₹' . number_format($fs->target_amount ?? 0, 2))
                ->addColumn('variance', function($fs) {
                    $achieved = $fs->achievement ?? 0;
                    $target = $fs->target_amount ?? 0;
                    if ($target == 0) return ($achieved > 0) ? '<span class="text-success small">+100% (No Target)</span>' : '<span class="text-muted small">0%</span>';
                    
                    $percent = ($achieved / $target) * 100;
                    $color = ($percent >= 100) ? 'text-success' : (($percent >= 70) ? 'text-warning' : 'text-danger');
                    return "<span class='{$color} fw-bold'>" . number_format($percent, 1) . "%</span>";
                })
                ->addColumn('progress_bar', function($fs) {
                    $achieved = $fs->achievement ?? 0;
                    $target = $fs->target_amount ?? 0;
                    if ($target == 0) $percent = ($achieved > 0) ? 100 : 0;
                    else $percent = min(($achieved / $target) * 100, 100);
                    
                    $color = ($percent >= 100) ? 'bg-success' : (($percent >= 70) ? 'bg-warning' : 'bg-danger');
                    return '<div class="progress" style="height: 6px;"><div class="progress-bar ' . $color . '" role="progressbar" style="width: ' . $percent . '%"></div></div>';
                })
                ->rawColumns(['variance', 'progress_bar'])
                ->make(true);
        }

        $salesManagers = SalesManager::with('user')->get();
        return view('admin.reports.targets', compact('salesManagers'));
    }

    public function performanceReports()
    {
        return redirect()->route('admin.reports.fieldstaffs');
    }

    /**
     * GPS Tracking & Route Path Visualization
     */
    /**
     * Live Monitoring Dashboard for Field Staff
     */
    public function monitoring()
    {
        $user = Auth::user();
        abort_if(!$user->hasPermissionToCategory('performance_reports', 'view') && !$user->hasPermissionToCategory('executive_reports', 'view'), 403);
        $salesManagers = SalesManager::with('user')->get();
        return view('admin.reports.monitoring', compact('salesManagers'));
    }

    /**
     * AJAX Endpoint for Monitoring Data
     */
    public function getMonitoringData(Request $request)
    {
        $user = Auth::user();
        
        // Scope staff by manager if applicable
        $staffQuery = FieldStaff::with(['user', 'salesManager.user']);
        $this->applyGlobalFilters($staffQuery, $request);
        $staffUsers = $staffQuery->get();

        $today = now()->toDateString();
        $data = [];
        $alerts = [];

        foreach ($staffUsers as $fs) {
            $fsUser = $fs->user;
            if (!$fsUser) continue;

            // 1. Get Latest Location Today
            $lastLoc = \App\Models\LocationLog::where('user_id', $fsUser->id)
                ->whereDate('timestamp', $today)
                ->latest('timestamp')
                ->first();

            // 2. Get Last Attendance Action
            $lastAttendance = \App\Models\AttendanceLog::where('user_id', $fsUser->id)
                ->whereDate('timestamp', $today)
                ->latest('timestamp')
                ->first();

            // 3. Visit Stats
            $visitCount = \App\Models\VisitLog::where('user_id', $fsUser->id)
                ->whereDate('check_in_at', $today)
                ->count();

            $ongoingVisit = \App\Models\VisitLog::where('user_id', $fsUser->id)
                ->whereDate('check_in_at', $today)
                ->whereNull('check_out_at')
                ->first();

            // 4. Calculate Distance
            $distance = \App\Models\LocationLog::calculateDailyDistance($fsUser->id, $today);

            // 5. Determine Status & Alerts
            $status = 'offline';
            $statusColor = '#95a5a6'; // Gray
            
            if ($lastAttendance && $lastAttendance->type === 'punch_in') {
                $status = 'online';
                $statusColor = '#2ecc71'; // Green
                
                if ($lastLoc) {
                    $diffInMins = $lastLoc->timestamp->diffInMinutes(now());
                    if ($diffInMins > 45) {
                        $status = 'idle';
                        $statusColor = '#f1c40f'; // Yellow
                        $alerts[] = [
                            'staff_id' => $fs->id,
                            'staff_name' => $fsUser->name,
                            'type' => 'inactivity',
                            'message' => "{$fsUser->name} has been stationary for {$diffInMins} minutes.",
                            'time' => $lastLoc->timestamp->format('H:i')
                        ];
                    }
                }
                
                if ($ongoingVisit) {
                    $status = 'visiting';
                    $statusColor = '#9b59b6'; // Purple
                }
            }

            // Check for Mock Location alerting
            $mockAlertToday = \App\Models\LocationLog::where('user_id', $fsUser->id)
                ->whereDate('timestamp', $today)
                ->where('is_mock_location', true)
                ->exists();

            if ($mockAlertToday) {
                $alerts[] = [
                    'staff_id' => $fs->id,
                    'staff_name' => $fsUser->name,
                    'type' => 'mock_gps',
                    'message' => "Possible Mock GPS usage detected for {$fsUser->name} today.",
                    'time' => now()->format('H:i')
                ];
            }

            $data[] = [
                'id' => $fs->id,
                'user_id' => $fsUser->id,
                'name' => $fsUser->name,
                'avatar' => $fsUser->avatar_url,
                'manager' => $fs->salesManager?->user?->name ?? 'N/A',
                'lat' => $lastLoc->latitude ?? null,
                'lng' => $lastLoc->longitude ?? null,
                'last_seen' => $lastLoc ? $lastLoc->timestamp->diffForHumans() : 'Never today',
                'status' => $status,
                'status_color' => $statusColor,
                'stats' => [
                    'visits' => $visitCount,
                    'distance' => $distance . ' KM'
                ],
                'ongoing_visit' => $ongoingVisit ? $ongoingVisit->customer_name : null
            ];
        }

        return response()->json([
            'staff' => $data,
            'alerts' => $alerts,
            'timestamp' => now()->format('H:i:s')
        ]);
    }

    public function fieldStaffTracking(Request $request)
    {
        $userId = $request->user_id;
        $date = $request->date ?? now()->toDateString();
        
        $user = \App\Models\User::findOrFail($userId);
        
        // Fetch logs for the day
        $locations = \App\Models\LocationLog::where('user_id', $userId)
            ->whereDate('timestamp', $date)
            ->orderBy('timestamp', 'asc')
            ->get();
            
        $punches = \App\Models\AttendanceLog::where('user_id', $userId)
            ->whereDate('timestamp', $date)
            ->orderBy('timestamp', 'asc')
            ->get();
            
        $visits = \App\Models\VisitLog::where('user_id', $userId)
            ->whereDate('check_in_at', $date)
            ->get();

        // Calculate total distance coverd
        $totalDistance = \App\Models\LocationLog::calculateDailyDistance($userId, $date);

        $lastPunch = $punches->last();
        $isOnline = $lastPunch && $lastPunch->type === 'punch_in';

        return view('admin.reports.fieldstaff_tracking', compact('user', 'locations', 'punches', 'visits', 'date', 'totalDistance', 'isOnline'));
    }

    public function downloadExport(Request $request, $format)
    {
        $type = $request->report_type ?? 'orders';
        
        $query = $this->getExportQuery($type, $request);
        $data = $query->get();

        // Professional Title & Subtitle Mapping
        $titleMapping = [
            'orders' => [
                'title' => ($request->order_type === 'distributor' ? 'Distributor' : 'Retailer') . ' Sales Intelligence Report',
                'subtitle' => 'Detailed transactional records and distribution flow analysis'
            ],
            'distributors' => [
                'title' => 'Distributor Performance Registry',
                'subtitle' => 'Strategic analysis of distribution channel reach and volume'
            ],
            'retailers' => [
                'title' => 'Retailer Commercial Overview',
                'subtitle' => 'Census of active pharmacy outlets and procurement frequency'
            ],
            'products' => [
                'title' => 'Pharmaceutical Movement Matrix',
                'subtitle' => 'Product-level SKU performance and inventory turnover'
            ],
            'fieldstaffs' => [
                'title' => 'Personnel Performance & Productivity Matrix',
                'subtitle' => 'Comprehensive analysis of staff productivity, retailer coverage, and revenue'
            ]
        ];

        $displayTitle = $titleMapping[$type]['title'] ?? (ucfirst($type) . " Analysis Report");
        $displaySubTitle = $titleMapping[$type]['subtitle'] ?? "Comprehensive system generated commercial report";

        // Refine Filter Context for Professional Appearance
        $filterContext = [];
        if ($request->sales_manager_id) $filterContext['Executive Manager'] = \App\Models\SalesManager::find($request->sales_manager_id)->user->name ?? 'N/A';
        if ($request->fieldstaff_id) $filterContext['Field Personnel'] = \App\Models\FieldStaff::find($request->fieldstaff_id)->user->name ?? 'N/A';
        if ($request->distributor_id) $filterContext['Distributor'] = \App\Models\Distributor::find($request->distributor_id)->user->name ?? 'N/A';
        if ($request->retailer_id) $filterContext['Retailer Shop'] = \App\Models\Retailer::find($request->retailer_id)->shop_name ?? 'N/A';
        if ($request->status) $filterContext['Order Status'] = ucfirst($request->status);
        
        // Human-friendly Period formatting
        [$f, $t] = $this->getFilterDates($request);
        $period = $request->period ?? 'all';
        if ($f && $t) {
            if ($period === 'today') $filterContext['Report Period'] = "Daily: " . $f->format('M d, Y');
            elseif ($period === 'yesterday') $filterContext['Report Period'] = "Daily: " . $f->format('M d, Y');
            elseif ($period === 'this_month') $filterContext['Report Period'] = "Monthly: " . $f->format('F Y');
            elseif ($period === '7days') $filterContext['Report Period'] = "Last 7 Days (Until " . $t->format('M d') . ")";
            else $filterContext['Custom Range'] = $f->format('d/m/Y') . ' - ' . $t->format('d/m/Y');
        } else {
            $filterContext['Report Period'] = "Historical (All Time)";
        }

        // Apply Ordering
        $query = $this->applyExportOrdering($query, $type, $request);
        $data = $query->get();

        if ($format === 'csv') {
            return $this->exportToCsv($data, $type, $filterContext, $displayTitle);
        } else {
            return $this->exportToPdf($data, $type, $filterContext, $displayTitle, $displaySubTitle);
        }
    }

    protected function getExportQuery($type, Request $request)
    {
        switch ($type) {
            case 'orders':
                $query = RetailerOrder::with(['retailer.user', 'distributor.user', 'fieldStaff.user', 'items.product']);
                return $this->applyGlobalFilters($query, $request);
            case 'distributors':
                $query = Distributor::with('user');
                if ($request->sales_manager_id) {
                    $query->where('sales_manager_id', $request->sales_manager_id);
                }
                
                $orderType = $request->order_type ?? 'retailer';
                $rel = ($orderType === 'distributor') ? 'distributorOrders' : 'retailerOrders';

                return $query->withSum([$rel . ' as total_sales' => function($q) use ($request) {
                    $this->applyGlobalFilters($q, $request);
                }], 'total_amount')->withCount([$rel . ' as total_orders' => function($q) use ($request) {
                    $this->applyGlobalFilters($q, $request);
                }]);
            case 'retailers':
                $query = Retailer::with(['user', 'fieldStaff.user']);
                $this->applyGlobalFilters($query, $request, null);
                return $query->withSum(['orders as total_sales' => function($q) use ($request) {
                    $this->applyGlobalFilters($q, $request);
                }], 'total_amount')->withCount(['orders as total_orders' => function($q) use ($request) {
                    $this->applyGlobalFilters($q, $request);
                }]);
            case 'products':
                $orderType = $request->order_type ?? 'retailer';
                $orderModel = ($orderType === 'distributor') ? \App\Models\DistributorOrder::class : \App\Models\RetailerOrder::class;
                $orderItemModel = ($orderType === 'distributor') ? \App\Models\DistributorOrderItem::class : \App\Models\RetailerOrderItem::class;
                $orderRel = ($orderType === 'distributor') ? 'distributorOrder' : 'retailerOrder';
                $totalAmountCol = ($orderType === 'distributor') ? 'subtotal' : 'total_amount';

                return \App\Models\Product::select('products.*')
                    ->selectSub(
                        $orderItemModel::whereColumn('product_id', 'products.id')
                            ->whereHas($orderRel, function($q) use ($request) {
                                $this->applyGlobalFilters($q, $request);
                                $q->where('status', 'delivered');
                            })->selectRaw('SUM(quantity)'), 
                        'total_sold'
                    )
                    ->selectSub(
                        $orderItemModel::whereColumn('product_id', 'products.id')
                            ->whereHas($orderRel, function($q) use ($request) {
                                $this->applyGlobalFilters($q, $request);
                                $q->where('status', 'delivered');
                            })->selectRaw('SUM(free_quantity)'), 
                        'total_free'
                    )
                    ->selectSub(
                        $orderItemModel::whereColumn('product_id', 'products.id')
                            ->whereHas($orderRel, function($q) use ($request) {
                                $this->applyGlobalFilters($q, $request);
                                $q->where('status', 'delivered');
                            })->selectRaw("SUM({$totalAmountCol})"), 
                        'total_revenue'
                    )
                    ->selectSub(
                        $orderItemModel::whereColumn('product_id', 'products.id')
                            ->whereHas($orderRel, function($q) use ($request) {
                                $this->applyGlobalFilters($q, $request);
                                $q->where('status', 'delivered');
                            })->selectRaw('COUNT(DISTINCT ' . (($orderType === 'distributor') ? 'distributor_order_id' : 'retailer_order_id') . ')'), 
                        'order_count'
                    );
            case 'fieldstaffs':
                $query = \App\Models\FieldStaff::with(['user', 'salesManager.user']);
                if ($request->sales_manager_id) {
                    $query->where('sales_manager_id', $request->sales_manager_id);
                }
                $orderType = $request->order_type ?? 'retailer';
                if ($orderType === 'distributor') {
                    return $query->withCount(['retailers as total_retailers']);
                }

                return $query->withSum(['retailerOrders as total_revenue' => function($q) use ($request) {
                    $this->applyGlobalFilters($q, $request);
                }], 'total_amount')->withCount(['retailerOrders as total_orders' => function($q) use ($request) {
                    $this->applyGlobalFilters($q, $request);
                }])->withCount(['retailers as total_retailers']);
            default:
                return RetailerOrder::query();
        }
    }

    protected function applyExportOrdering($query, $type, Request $request)
    {
        $colIndex = $request->order_col;
        $direction = $request->order_dir === 'asc' ? 'asc' : 'desc';

        if ($colIndex === null) {
            return $query;
        }

        $mapping = $this->getSortMapping($type);
        if (isset($mapping[$colIndex])) {
            $column = $mapping[$colIndex];
            
            // Check if column exists in the database or as an alias
            // Simple approach for aliases
            $query->orderBy($column, $direction);
        }

        return $query;
    }

    protected function getSortMapping($type)
    {
        switch ($type) {
            case 'products':
                return [
                    1 => 'product_code',
                    2 => 'product_name',
                    3 => 'total_sold',
                    4 => 'total_free',
                    5 => 'order_count',
                    6 => 'total_revenue'
                ];
            case 'distributors':
                return [
                    1 => 'name',
                    3 => 'total_orders',
                    4 => 'total_sales'
                ];
            case 'orders':
                return [
                    0 => 'order_code',
                    1 => 'placed_at',
                    5 => 'total_amount',
                    6 => 'status'
                ];
            case 'retailers':
                return [
                    1 => 'shop_name',
                    3 => 'total_orders',
                    4 => 'total_sales'
                ];
            case 'fieldstaffs':
                return [
                    1 => 'name',
                    3 => 'total_retailers',
                    4 => 'total_orders',
                    5 => 'total_revenue'
                ];
            default:
                return [];
        }
    }

    protected function exportToCsv($data, $type, $filters = [], $title = null)
    {
        $filename = "{$type}_report_" . now()->format('Y-m-d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($data, $type, $filters, $title) {
            $file = fopen('php://output', 'w');
            
            // Add Title if provided
            if ($title) {
                fputcsv($file, ["REPORT: " . strtoupper($title)]);
                fputcsv($file, []); // Spacer
            }

            // Add filter context as a professional summary block
            if (!empty($filters)) {
                fputcsv($file, ["REPORT PARAMETERS:"]);
                foreach ($filters as $key => $val) {
                    fputcsv($file, [" - {$key}: {$val}"]);
                }
                fputcsv($file, []); // Spacer
            }

            if ($type === 'orders') {
                fputcsv($file, ['Order Code', 'Invoice No', 'Date', 'Retailer', 'Distributor', 'Staff', 'Volume (Units)', 'SKUs', 'Total Revenue', 'Tax Component', 'Status', 'Fulfillment Time']);
                foreach ($data as $row) {
                    $tax = $row->items->sum(fn($i) => ($i->product->gst / 100) * ($i->product->taxable_value * $i->quantity));
                    fputcsv($file, [
                        $row->order_code,
                        $row->invoice_no ?? 'N/A',
                        $row->placed_at->format('Y-m-d H:i'),
                        $row->retailer->user->name ?? 'N/A',
                        $row->distributor->user->name ?? 'N/A',
                        $row->fieldStaff->user->name ?? 'N/A',
                        $row->total_quantity,
                        $row->total_items,
                        $row->total_amount,
                        number_format($tax, 2),
                        $row->status,
                        $row->delivered_at ? ($row->placed_at->diffInDays($row->delivered_at) >= 1 ? ($d = $row->placed_at->diffInDays($row->delivered_at)) . ' ' . ($d == 1 ? 'day' : 'days') : $row->placed_at->diffForHumans($row->delivered_at, true)) : 'Pending'
                    ]);
                }
            } elseif ($type === 'distributors') {
                fputcsv($file, ['Rank', 'Distributor Name', 'Email', 'Active Network (Retailers)', 'Total Orders', 'Total Sales Volume']);
                foreach ($data as $i => $row) {
                    $rel = ($request->order_type ?? 'retailer') === 'distributor' ? 'distributorOrders' : 'retailerOrders';
                    $reach = $row->$rel()->distinct('retailer_id')->count();
                    fputcsv($file, [
                        $i+1, 
                        $row->user->name ?? $row->name, 
                        $row->user->email, 
                        $reach,
                        $row->total_orders, 
                        $row->total_sales
                    ]);
                }
            } elseif ($type === 'retailers') {
                fputcsv($file, ['Rank', 'Shop Name', 'Location/Area', 'Field Staff', 'GST Number', 'Drug License', 'Total Orders', 'Total Sales']);
                foreach ($data as $i => $row) {
                    fputcsv($file, [
                        $i+1, 
                        $row->shop_name, 
                        $row->area->name ?? 'N/A',
                        $row->fieldStaff->name ?? 'N/A', 
                        $row->gst,
                        $row->drug_license_no,
                        $row->total_orders, 
                        $row->total_sales
                    ]);
                }
            } elseif ($type === 'products') {
                fputcsv($file, ['Rank', 'SKU/Code', 'Medicine Name', 'PTR', 'MRP', 'Units Sold', 'Free Qty', 'Intensity (Avg/Ord)', 'Total Orders', 'Revenue']);
                foreach ($data as $i => $row) {
                    fputcsv($file, [
                        $i+1, 
                        $row->product_code, 
                        $row->product_name, 
                        $row->ptr,
                        $row->mrp,
                        $row->total_sold,
                        $row->total_free,
                        $row->order_count ? number_format($row->total_sold / $row->order_count, 1) : 0,
                        $row->order_count,
                        $row->total_revenue
                    ]);
                }
            } elseif ($type === 'fieldstaffs') {
                fputcsv($file, ['Rank', 'Staff Member', 'Sales Manager', 'Outlets Covered', 'Engagement (Ord/Outlet)', 'AOV (Avg Order Value)', 'Total Orders', 'Revenue']);
                foreach ($data as $i => $row) {
                    fputcsv($file, [
                        $i+1, 
                        $row->user->name ?? 'N/A', 
                        $row->salesManager->user->name ?? 'N/A',
                        $row->total_retailers,
                        number_format($row->total_orders / max($row->total_retailers, 1), 1),
                        $row->total_orders ? number_format($row->total_revenue / $row->total_orders, 2) : 0,
                        $row->total_orders,
                        $row->total_revenue
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    protected function exportToPdf($data, $type, $filters = [], $title = null, $subtitle = null)
    {
        $pdf = Pdf::loadView('admin.reports.pdf.report_template', [
            'data' => $data,
            'type' => $type,
            'filters' => $filters,
            'isManagement' => $this->isManagement(),
            'title' => $title ?? (ucfirst($type) . " Analysis Report"),
            'subtitle' => $subtitle,
            'date' => now()->format('M d, Y')
        ])->setPaper('a4', 'landscape');

        return $pdf->download("{$type}_report_" . now()->format('Y-m-d') . ".pdf");
    }
}
