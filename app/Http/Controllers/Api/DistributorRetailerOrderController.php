<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RetailerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HandlesNotifications;
use App\Services\OcrService;

class DistributorRetailerOrderController extends Controller
{
    use HandlesNotifications;

    protected $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }
    /**
     * @OA\Get(
     *     path="/api/distributor/retailer-orders",
     *     summary="List retailer orders placed to the authenticated distributor",
     *     tags={"Distributor Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="retailer_id", in="query", required=false, description="Filter by retailer ID", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filter by order status (pending, processing, accepted, delivered, cancelled)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="payment_status", in="query", required=false, description="Filter by payment status: paid or pending (unpaid)", @OA\Schema(type="string", enum={"paid","pending"})),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Results per page (default 15)", @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of retailer orders",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="order_code", type="string"),
     *                 @OA\Property(property="retailer_id", type="integer"),
     *                 @OA\Property(property="retailer_name", type="string"),
     *                 @OA\Property(property="retailer_shop", type="string"),
     *                 @OA\Property(property="total_amount", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="items_count", type="integer"),
     *                 @OA\Property(property="payment_method", type="string"),
     *                 @OA\Property(property="placed_at", type="string", format="date-time")
     *             )),
     *             @OA\Property(property="total", type="integer"),
     *             @OA\Property(property="current_page", type="integer"),
     *             @OA\Property(property="last_page", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Not a distributor")
     * )
     */
    public function index(Request $request)
    {
        $user = auth('api')->user();

        if (!$user || !$user->distributor) {
            return response()->json(['message' => 'Authenticated user is not a distributor.'], 403);
        }

        $distributorId = $user->distributor->id;

        $query = RetailerOrder::with([
            'retailer.user',
            'fieldStaff.user',
            'items.product',
        ])
            ->where('distributor_id', $distributorId)
            ->latest('placed_at');

        // Optional: filter by retailer
        if ($request->filled('retailer_id')) {
            $query->where('retailer_id', (int) $request->retailer_id);
        }

        // Optional: filter by order status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Optional: filter by payment status
        if ($request->filled('payment_status')) {
            $ps = $request->payment_status;
            if ($ps === 'pending') {
                $query->where(function ($q) {
                    $q->where('payment_status', 'pending')->orWhereNull('payment_status');
                });
            } else {
                $query->where('payment_status', $ps);
            }
        }

        $perPage = (int) $request->get('per_page', 15);
        $orders  = $query->paginate($perPage);

        $data = $orders->map(function ($order) {
            return [
                'id'              => $order->id,
                'order_code'      => $order->order_code,
                'retailer_id'     => $order->retailer_id,
                'retailer_name'   => $order->retailer?->user?->name ?? 'N/A',
                'retailer_shop'   => $order->retailer?->shop_name ?? 'N/A',
                'field_staff'     => $order->fieldStaff?->user?->name ?? 'N/A',
                'total_amount'    => number_format($order->total_amount, 2),
                'total_items'     => $order->total_items,
                'total_quantity'  => $order->total_quantity,
                'status'          => $order->status,
                'payment_status'  => $order->payment_status ?? 'pending',
                'invoice_url'     => $order->invoice_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($order->invoice_path) : null,
                'placed_at'       => $order->placed_at?->format('Y-m-d H:i:s'),
                'delivered_at'    => $order->delivered_at?->format('Y-m-d H:i:s'),
                'notes'           => $order->notes,
                'delivery_notes'  => $order->delivery_notes,
                'items'           => $order->items->map(function ($item) {
                    return [
                        'product_id'   => $item->product_id,
                        'product_name' => $item->product?->product_name ?? 'N/A',
                        'quantity'     => $item->quantity,
                        'unit_price'   => $item->unit_price,
                        'total_amount' => $item->total_amount,
                    ];
                }),
            ];
        });

        return response()->json([
            'data'         => $data,
            'current_page' => $orders->currentPage(),
            'per_page'     => $orders->perPage(),
            'total'        => $orders->total(),
            'last_page'    => $orders->lastPage(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/distributor/retailer-orders/{id}",
     *     summary="Get a single retailer order detail with Web-parity details",
     *     description="Includes item details, tax breakdown, retailer info, and available batches for approval selection.",
     *     tags={"Distributor Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Retailer Order ID", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detailed order info"),
     *     @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show($id)
    {
        $user = auth('api')->user();
        if (!$user || !$user->distributor) {
            return response()->json(['message' => 'Authenticated user is not a distributor.'], 403);
        }

        $order = RetailerOrder::with(['retailer.user', 'fieldStaff.user', 'items.product', 'items.batches'])
            ->where('id', $id)
            ->where('distributor_id', $user->distributor->id)
            ->firstOrFail();

        $distributor = $user->distributor;

        // Calculate Tax Details (Parity with web invoice)
        $cgstRate = 9; // Default as per web
        $sgstRate = 9;
        $divisor = 1 + (($cgstRate + $sgstRate) / 100);
        $taxableAmount = $order->total_amount / $divisor;
        $cgstAmount = $taxableAmount * ($cgstRate / 100);
        $sgstAmount = $taxableAmount * ($sgstRate / 100);

        return response()->json([
            'id'             => $order->id,
            'order_code'     => $order->order_code,
            'status'         => $order->status,
            'payment_status' => $order->payment_status ?? 'pending',

            'retailer' => [
                'id' => $order->retailer_id,
                'name' => $order->retailer?->user?->name ?? 'N/A',
                'description' => $order->retailer?->shop_name ?? 'N/A',
                'address' => $order->retailer?->address ?? $order->retailer?->shop_address ?? 'N/A',
                'gst' => $order->retailer?->gst ?? 'N/A',
                'drug_license_no' => $order->retailer?->drug_license_no ?? 'N/A',
                'phone' => $order->retailer?->contact_no ?? $order->retailer?->phone ?? 'N/A',
            ],

            'summary' => [
                'total_items' => $order->total_items,
                'total_quantity' => $order->total_quantity,
                'taxable_amount' => number_format($taxableAmount, 2, '.', ''),
                'cgst' => number_format($cgstAmount, 2, '.', ''),
                'sgst' => number_format($sgstAmount, 2, '.', ''),
                'total_amount' => number_format($order->total_amount, 2, '.', ''),
                'loyalty_points' => (float)($order->loyalty_points_earned ?? 0),
            ],

            'items' => $order->items->map(function ($item) use ($distributor, $order) {
                $itemData = [
                    'id'               => $item->id,
                    'product_id'       => $item->product_id,
                    'product_name'     => $item->product?->product_name ?? 'N/A',
                    'quantity'         => $item->quantity,
                    'unit'             => $item->unit,
                    'unit_price'       => $item->unit_price,
                    'total_amount'     => $item->total_amount,
                    'allocated_batches' => $item->batches->map(function ($b) {
                        return [
                            'batch_no' => $b->batch_no,
                            'expiry_date' => $b->expiry_date ? (function ($date) {
                                $parsed = \Carbon\Carbon::parse($date);
                                if ($parsed->copy()->endOfMonth()->isSameDay($parsed)) {
                                    return $parsed->format('m/Y');
                                }
                                return $parsed->format('d/m/Y');
                            })($b->expiry_date) : '-',
                            'quantity' => $b->quantity
                        ];
                    }),
                ];

                // If pending/processing, include available batches for selection
                if (in_array($order->status, ['pending', 'processing'])) {
                    $itemData['available_batches'] = \App\Models\Inventory::where('distributor_id', $distributor->id)
                        ->where('product_id', $item->product_id)
                        ->where('stock', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->get()
                        ->map(function ($inv) {
                            return [
                                'inventory_id' => $inv->id,
                                'batch_no' => $inv->batch_no,
                                'expiry_date' => $inv->expiry_date ? (function ($date) {
                                    $parsed = \Carbon\Carbon::parse($date);
                                    if ($parsed->copy()->endOfMonth()->isSameDay($parsed)) {
                                        return $parsed->format('m/Y');
                                    }
                                    return $parsed->format('d/m/Y');
                                })($inv->expiry_date) : '-',
                                'stock' => $inv->stock, // Note: This is in strips/base unit
                            ];
                        });
                } else {
                    $itemData['available_batches'] = [];
                }

                return $itemData;
            }),

            'invoice_url'    => $order->invoice_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($order->invoice_path) : null,
            'placed_at'      => $order->placed_at?->format('Y-m-d H:i:s'),
            'notes'          => $order->notes,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/distributor/retailer-orders/{id}/accept",
     *     summary="Accept/Approve a retailer order",
     *     description="Accepts the order. If invoice was already uploaded via /upload-invoice, it will use that. Otherwise, a new invoice must be provided.",
     *     tags={"Distributor Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Retailer Order ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="invoice", type="string", format="binary", description="Invoice file (optional if already uploaded)"),
     *                 @OA\Property(property="payment_status", type="string", enum={"pending","paid"})
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order accepted successfully"),
     *     @OA\Response(response=422, description="Validation or stock error")
     * )
     */
    public function acceptOrder(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user || !$user->distributor) {
            return response()->json(['message' => 'Authenticated user is not a distributor.'], 403);
        }

        $retailerOrder = RetailerOrder::where('id', $id)
            ->where('distributor_id', $user->distributor->id)
            ->firstOrFail();

        if ($retailerOrder->status !== 'processing') {
            return response()->json(['error' => 'Order must be in processing status to be approved.'], 400);
        }

        $request->validate([
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'payment_status' => 'nullable|string|in:pending,paid',
        ]);

        if (!$retailerOrder->invoice_path && !$request->hasFile('invoice')) {
            return response()->json(['error' => 'Invoice file is required because it hasn\'t been uploaded yet.'], 422);
        }

        $invoicePath = $retailerOrder->invoice_path;
        if ($request->hasFile('invoice')) {
            $file = $request->file('invoice');
            $filename = 'invoice_' . $retailerOrder->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $invoicePath = $file->storeAs('retailer_invoices', $filename, 'public');
        }

        try {
            $result = $this->processOrderAcceptance(
                $retailerOrder,
                null, // Deprecated manual batch selection; forces FEFO
                $request->payment_status,
                $invoicePath
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/distributor/retailer-orders/{id}/upload-invoice",
     *     summary="Upload invoice and trigger OCR for auto-approval",
     *     tags={"Distributor Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Retailer Order ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="invoice", type="string", format="binary", description="Invoice file")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order accepted automatically if OCR matches"),
     *     @OA\Response(response=422, description="OCR mismatch or validation error")
     * )
     */
    public function uploadInvoice(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user || !$user->distributor) {
            return response()->json(['message' => 'Authenticated user is not a distributor.'], 403);
        }

        $retailerOrder = RetailerOrder::with('items.product')
            ->where('id', $id)
            ->where('distributor_id', $user->distributor->id)
            ->firstOrFail();

        if ($retailerOrder->status !== 'processing') {
            return response()->json(['error' => 'Order must be in processing status to upload invoice.'], 400);
        }

        $request->validate([
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('invoice');
        $filename = 'invoice_' . $retailerOrder->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('retailer_invoices', $filename, 'public');

        // Extract OCR data
        $ocrData = $this->ocrService->processInvoice($file, 'retailer');

        // Normalize OCR data key (line_items, items, or medicines)
        $ocrItemsRaw = $ocrData['line_items'] ?? $ocrData['items'] ?? $ocrData['medicines'] ?? null;
        if (!$ocrData || !$ocrItemsRaw) {
            // Save path anyway for manual approval later
            $retailerOrder->update(['invoice_path' => $path]);
            Log::warning('OCR Extraction Failed or Empty', ['order_id' => $id, 'raw_data' => $ocrData]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to extract data from the invoice or invalid OCR response.',
                'invoice_path' => $path
            ], 422);
        }

        // Matching Logic
        $isMatch = true;
        $mismatches = [];
        $ocrItems = collect($ocrItemsRaw);

        $expectedData = [];
        $matchedDetails = [];

        foreach ($retailerOrder->items as $orderItem) {
            $productName = $orderItem->product->product_name;
            $expectedQty = (float)$orderItem->quantity;

            // Normalize names for comparison (lowercase, trimmed)
            $normalizedOrderName = strtolower(trim($productName));

            // Find matching item in OCR
            $match = $ocrItems->first(function ($item) use ($normalizedOrderName) {
                // Assuming OCR item has 'product_name' or 'name' or 'description'
                $name = strtolower(trim($item['product_name'] ?? $item['name'] ?? $item['description'] ?? ''));

                // 1. Direct contains check
                if (str_contains($name, $normalizedOrderName) || str_contains($normalizedOrderName, $name)) {
                    return true;
                }

                // 2. Fuzzy match: Check if at least first 2 words match
                $ocrWords = preg_split('/[\s,]+/', $name);
                $orderWords = preg_split('/[\s,]+/', $normalizedOrderName);

                if (count($ocrWords) >= 2 && count($orderWords) >= 2) {
                    $matchCount = 0;
                    $wordsToCheck = min(count($ocrWords), count($orderWords), 4);
                    for ($i = 0; $i < $wordsToCheck; $i++) {
                        if (isset($ocrWords[$i]) && isset($orderWords[$i]) && $ocrWords[$i] === $orderWords[$i]) {
                            $matchCount++;
                        }
                    }
                    if ($matchCount >= 2) return true;
                }

                return false;
            });
            if (!$match) {
                $isMatch = false;
                $mismatches[] = "Product '{$productName}' not found in invoice.";

                $matchedDetails[] = [
                    'order_item_id' => $orderItem->id,
                    'product_name' => $productName,
                    'expected_qty' => $expectedQty,
                    'status' => 'missing',
                    'ocr_data' => null
                ];
            } else {
                $ocrQty = (float)($match['quantity'] ?? $match['qty'] ?? 0);
                $freeQty = (float)($match['sch'] ?? $match['free_qty'] ?? 0);

                $detail = [
                    'order_item_id' => $orderItem->id,
                    'product_name' => $productName,
                    'expected_qty' => $expectedQty,
                    'status' => 'matched',
                    'ocr_data' => [
                        'description' => $match['description'] ?? $match['product_name'] ?? $match['name'] ?? 'N/A',
                        'batch' => $match['batch'] ?? 'N/A',
                        'expiry' => $match['expiry'] ?? 'N/A',
                        'qty' => $ocrQty,
                        'free_qty' => $freeQty,
                        'taxable_amt' => (float)($match['taxable_amt'] ?? $match['amount'] ?? 0),
                        'gst_percent' => (float)($match['gst'] ?? 0),
                        'total_amt' => (float)($match['total_amount'] ?? $match['amount'] ?? 0),
                    ]
                ];

                if ($ocrQty < $expectedQty) {
                    $isMatch = false;
                    $mismatches[] = "Quantity mismatch for '{$productName}': Expected {$expectedQty}, found {$ocrQty}.";
                    $detail['status'] = 'mismatch';
                }

                $matchedDetails[] = $detail;
            }

            $expectedData[] = [
                'id' => $orderItem->id,
                'product_name' => $productName,
                'quantity' => $expectedQty
            ];
        }

        // Always save path for manual approval later
        $retailerOrder->update(['invoice_path' => $path]);

        if ($isMatch) {
            return response()->json([
                'status' => 'success',
                'message' => 'Invoice matched successfully! You can now proceed to accept the order.',
                'matched_details' => $matchedDetails,
                'ocr_data' => $ocrItemsRaw // Line items
            ]);
        }

        // Mismatch: return error with data for cross-verification
        return response()->json([
            'status' => 'error',
            'message' => 'Invoice data mismatch. Please cross-verify and approve manually.',
            'mismatches' => $mismatches,
            'matched_details' => $matchedDetails,
            'ocr_data' => $ocrItemsRaw, // Line items
            'data' => $ocrData, // Whole OCR object
            'expected_data' => $expectedData,
            'invoice_path' => $path
        ], 422);
    }

    /**
     * Core logic for accepting a retailer order.
     * Extracted for reuse in auto-approval and manual approval.
     */
    protected function processOrderAcceptance(RetailerOrder $retailerOrder, $itemsBatches = null, $paymentStatus = 'pending', $invoicePath = null)
    {
        $distributor = $retailerOrder->distributor;

        DB::beginTransaction();
        try {
            // 1. Batch Allocation Logic
            if ($itemsBatches) {
                foreach ($itemsBatches as $allocation) {
                    $orderItem = $retailerOrder->items()->findOrFail($allocation['order_item_id']);
                    $product = $orderItem->product;

                    $multiplier = 1;
                    if ($orderItem->unit === 'Box') {
                        $multiplier = (int)($product->box_size ?? 1);
                    } elseif ($orderItem->unit === 'Carton') {
                        $multiplier = (int)($product->box_size ?? 1) * (int)($product->carton_size ?? 1);
                    }

                    $totalAllocated = 0;
                    $orderItem->batches()->delete();
                    foreach ($allocation['batches'] as $batchData) {
                        $inventory = \App\Models\Inventory::where('distributor_id', $distributor->id)
                            ->where('product_id', $product->id)
                            ->findOrFail($batchData['inventory_id']);

                        $deductQty = $batchData['quantity'] * $multiplier;

                        if ($inventory->stock < $deductQty) {
                            throw new \Exception("Not enough stock in batch {$inventory->batch_no} for product {$product->product_name}");
                        }

                        DB::table('inventories')->where('id', $inventory->id)->decrement('stock', $deductQty);

                        \App\Models\RetailerOrderItemBatch::create([
                            'retailer_order_item_id' => $orderItem->id,
                            'batch_no' => $inventory->batch_no,
                            'expiry_date' => $inventory->expiry_date,
                            'quantity' => $batchData['quantity'],
                        ]);

                        $totalAllocated += $batchData['quantity'];
                    }

                    if ($totalAllocated < $orderItem->quantity) {
                        throw new \Exception("Total allocated quantity ({$totalAllocated}) is less than ordered quantity ({$orderItem->quantity}) for {$product->product_name}");
                    }
                }
            } else {
                // Fallback to FEFO
                foreach ($retailerOrder->items as $orderItem) {
                    $product = $orderItem->product;
                    $orderItem->batches()->delete();
                    $multiplier = 1;
                    if ($orderItem->unit === 'Box') {
                        $multiplier = (int)($product->box_size ?? 1);
                    } elseif ($orderItem->unit === 'Carton') {
                        $multiplier = (int)($product->box_size ?? 1) * (int)($product->carton_size ?? 1);
                    }

                    $neededStrips = $orderItem->quantity * $multiplier;
                    $inventories = \App\Models\Inventory::where('distributor_id', $distributor->id)
                        ->where('product_id', $product->id)
                        ->where('stock', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->get();

                    if ($inventories->sum('stock') < $neededStrips) {
                        throw new \Exception("Insufficient total stock for product: {$product->product_name}");
                    }

                    $remainingStrips = $neededStrips;
                    foreach ($inventories as $inv) {
                        if ($remainingStrips <= 0) break;
                        $takeStrips = min($inv->stock, $remainingStrips);
                        DB::table('inventories')->where('id', $inv->id)->decrement('stock', $takeStrips);

                        \App\Models\RetailerOrderItemBatch::create([
                            'retailer_order_item_id' => $orderItem->id,
                            'batch_no' => $inv->batch_no,
                            'expiry_date' => $inv->expiry_date,
                            'quantity' => $takeStrips / $multiplier,
                        ]);

                        $remainingStrips -= $takeStrips;
                    }
                }
            }

            // 2. Update Order
            $updateData = [
                'status' => 'approved',
            ];
            if ($invoicePath) {
                $updateData['invoice_path'] = $invoicePath;
            }
            if ($paymentStatus) {
                $updateData['payment_status'] = $paymentStatus;
            }
            $retailerOrder->update($updateData);

            // 3. Loyalty Points Calculation
            $totalPoints = 0;
            foreach ($retailerOrder->items as $item) {
                if ($item->product) {
                    $ptr = (float) ($item->product->ptr ?? $item->product->mrp ?? 0);
                    $percentage = (float) $item->product->loyalty_point_percentage;
                    if ($percentage > 0 && $ptr > 0) {
                        $totalPoints += ($item->quantity * $ptr) * ($percentage / 100);
                    }
                }
            }
            if ($totalPoints > 0) {
                $retailerOrder->update(['loyalty_points_earned' => $totalPoints]);
                $retailer = $retailerOrder->retailer;
                if ($retailer) {
                    $retailer->increment('loyalty_points', $totalPoints);
                }
            }

            // 4. Notifications
            $this->clearOrderNotifications($retailerOrder->id, 'retailer_order');
            if ($retailerOrder->retailer && $retailerOrder->retailer->user) {
                $this->notifyUnique(
                    $retailerOrder->retailer->user,
                    new \App\Notifications\OrderActionRequired(
                        $retailerOrder,
                        "Your order #{$retailerOrder->order_code} has been accepted. Please confirm your order.",
                        url('/retailer/orders'),
                        'retailer_order'
                    )
                );
            }

            DB::commit();
            return [
                'success' => 'Order accepted successfully!',
                'loyalty_points_earned' => $totalPoints,
                'retailer_total_points' => $retailerOrder->retailer->loyalty_points ?? 0
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Process Retailer Order Approval failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/distributor/retailer-orders/{id}/reject",
     *     summary="Reject a retailer order",
     *     tags={"Distributor Retailer Orders"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Retailer Order ID", @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", minLength=5)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order rejected successfully"),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function rejectOrder(Request $request, $id)
    {
        $user = auth('api')->user();
        if (!$user || !$user->distributor) {
            return response()->json(['message' => 'Authenticated user is not a distributor.'], 403);
        }

        $retailerOrder = RetailerOrder::where('id', $id)
            ->where('distributor_id', $user->distributor->id)
            ->firstOrFail();

        if (!in_array($retailerOrder->status, ['pending', 'processing'])) {
            return response()->json(['error' => 'Only pending or processing orders can be rejected.'], 400);
        }

        $request->validate(['reason' => 'required|string|min:5']);

        $retailerOrder->update([
            'status' => 'rejected',
            'cancellation_reason' => $request->reason
        ]);

        $this->clearOrderNotifications($retailerOrder->id, 'retailer_order');
        if ($retailerOrder->retailer && $retailerOrder->retailer->user) {
            $this->notifyUnique(
                $retailerOrder->retailer->user,
                new \App\Notifications\OrderActionRequired(
                    $retailerOrder,
                    "Your order #{$retailerOrder->order_code} has been rejected by the distributor.",
                    url('/retailer/orders'),
                    'retailer_order'
                )
            );
        }

        return response()->json(['success' => 'Order rejected successfully.']);
    }

    private function clearOrderNotifications($orderId, $type)
    {
        if (method_exists($this, 'deleteOrderNotifications')) {
            $this->deleteOrderNotifications($orderId, $type);
        }
    }
}
