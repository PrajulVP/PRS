<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RetailerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Traits\HandlesNotifications;
use App\Traits\OneSignalNotifications;
use App\Traits\CalculatesPrices;
use App\Services\OcrService;

class DistributorRetailerOrderController extends Controller
{
    use HandlesNotifications, OneSignalNotifications, CalculatesPrices, \App\Traits\ConsolidatesFreeItems;

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
                'cancellation_reason' => $order->cancellation_reason,
                'invoice_url'     => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
                'placed_at'       => $order->placed_at?->format('Y-m-d H:i:s'),
                'delivered_at'    => $order->delivered_at?->format('Y-m-d H:i:s'),
                'delivery_notes'  => $order->delivery_notes,
                'items'           => $this->consolidateFreeItems($order->items->map(function ($item) {
                    return [
                        'product_id'   => $item->product_id,
                        'product_name' => $item->product?->product_name ?? 'N/A',
                        'quantity'     => $item->quantity,
                        'free_quantity' => $item->free_quantity,
                        'unit'         => $item->unit ?? 'Nos',
                        'side'         => $item->side,
                        'size'         => $item->size,
                        'free_side'    => $item->free_side,
                        'free_size'    => $item->free_size,
                        'unit_price'   => $item->unit_price,
                        'total_amount' => $item->total_amount,
                    ];
                }), true),
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
            'cancellation_reason' => $order->cancellation_reason,

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

            'items' => $this->consolidateFreeItems($order->items->map(function ($item) use ($distributor, $order) {
                $itemData = [
                    'id'               => $item->id,
                    'product_id'       => $item->product_id,
                    'product_name'     => $item->product?->product_name ?? 'N/A',
                    'quantity'         => $item->quantity,
                    'free_quantity'    => $item->free_quantity,
                    'unit'             => $item->unit,
                    'side'             => $item->side,
                    'size'             => $item->size,
                    'free_side'        => $item->free_side,
                    'free_size'        => $item->free_size,
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
            }), true),

            'invoice_url'    => $order->invoice_path ? asset('storage/' . $order->invoice_path) : null,
            'placed_at'      => $order->placed_at?->format('Y-m-d H:i:s'),
            'delivery_notes' => $order->delivery_notes,
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
        $fileHash = md5_file($file->getRealPath());

        // Check for duplicates across BOTH roles
        $existingRetailer = RetailerOrder::whereNotNull('metadata')
            ->where('id', '!=', $retailerOrder->id)
            ->whereJsonContains('metadata->invoice_hash', $fileHash)
            ->first();
        
        $existingDistributor = \App\Models\DistributorOrder::whereNotNull('metadata')
            ->whereJsonContains('metadata->invoice_hash', $fileHash)
            ->first();

        if ($existingRetailer || $existingDistributor) {
            $code = $existingRetailer ? $existingRetailer->order_code : $existingDistributor->order_code;
            $role = $existingRetailer ? 'Retailer' : 'Distributor';
            \Log::warning("Upload Invoice Failed: Duplicate Hash", ['order_id' => $id, 'duplicate_of' => $code]);
            return response()->json([
                'error' => "Invoice already uploaded for $role Order #$code. Duplicates are not allowed.",
                'duplicate' => true
            ], 422);
        }

        $filename = 'Invoice_' . $retailerOrder->order_code . '_' . date('Y-m-d_His') . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('invoices/retailers', $filename, 'public');

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
            // Only validate paid items against OCR. Free items are often merged into the 'sch' column.
            if ($orderItem->is_free) {
                continue;
            }

            $productName = $orderItem->product->product_name;
            $productCode = $orderItem->product->product_code;
            $expectedQty = (float)$orderItem->quantity;

            // Fetch distributor_product_code if inventory exists
            $inventory = \App\Models\Inventory::where('product_id', $orderItem->product_id)
                ->where('distributor_id', $retailerOrder->distributor_id)
                ->first();
            $distProductCode = $inventory ? $inventory->distributor_product_code : null;

            // Normalize names for comparison (lowercase, trimmed)
            $normalizedOrderName = strtolower(trim($productName));

            // Find matching item in OCR
            $match = $ocrItems->first(function ($item) use ($normalizedOrderName, $productCode, $distProductCode) {
                // 1. Code-based Match (Highest Priority)
                $ocrCode = trim($item['product_code'] ?? $item['item_code'] ?? $item['code'] ?? '');
                if (!empty($ocrCode)) {
                    if ((!empty($productCode) && strcasecmp($ocrCode, $productCode) === 0) ||
                        (!empty($distProductCode) && strcasecmp($ocrCode, $distProductCode) === 0)) {
                        return true;
                    }
                }

                $name = strtolower(trim($item['product_name'] ?? $item['name'] ?? $item['description'] ?? ''));
                $nameClean = trim(preg_replace('/[^a-z0-9]+/i', ' ', $name));
                $orderNameClean = trim(preg_replace('/[^a-z0-9]+/i', ' ', $normalizedOrderName));

                // 2. Direct contains check (cleaned)
                if (str_contains($nameClean, $orderNameClean) || str_contains($orderNameClean, $nameClean)) {
                    return true;
                }

                // 3. Fuzzy match: Check if at least 2 words intersect, regardless of order
                $ocrWords = array_filter(explode(' ', $nameClean));
                $orderWords = array_filter(explode(' ', $orderNameClean));
                
                $commonWords = array_intersect($ocrWords, $orderWords);
                if (count($commonWords) >= 2) {
                    return true;
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

                // Do NOT overwrite order free_quantity with OCR sch to preserve original order details.
                // Just update the product name if needed.
                $orderItem->update([
                    'product_name' => $orderItem->product_name ?? $productName
                ]);

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
                        'free_qty' => (float)($orderItem->free_quantity), // Do not show OCR free qty, show the originally placed free qty
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

        // Always save path and hash for manual approval and duplicate checks
        $meta = $retailerOrder->metadata ?? [];
        $meta['invoice_hash'] = $fileHash;
        $invoiceNo = $ocrData['invoice_metadata']['invoice_number'] ?? null;
        $retailerOrder->update([
            'invoice_path' => $path,
            'invoice_no' => $invoiceNo,
            'metadata' => $meta
        ]);

        if ($isMatch) {
            try {
                $result = $this->processOrderAcceptance($retailerOrder, null, 'pending', $path);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Invoice matched successfully and order has been auto-approved!',
                    'matched_details' => $matchedDetails,
                    'ocr_data' => $ocrItemsRaw, // Line items
                    'approval_result' => $result
                ]);
            } catch (\Exception $e) {
                \Log::error("Upload Invoice Auto-Approval Failed", ['order_id' => $id, 'error' => $e->getMessage()]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invoice matched, but auto-approval failed: ' . $e->getMessage(),
                    'invoice_path' => $path
                ], 422);
            }
        }

        // Mismatch: return error with data for cross-verification
        \Log::warning("Upload Invoice Mismatch", ['order_id' => $id, 'mismatches' => $mismatches]);
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
            $lockedOrder = \App\Models\RetailerOrder::where('id', $retailerOrder->id)->lockForUpdate()->first();
            if ($lockedOrder->status !== 'processing' && $lockedOrder->status !== 'pending') {
                DB::rollBack();
                return ['success' => false, 'message' => 'Order has already been processed.'];
            }
            // 0. Auto-Fix Legacy Orders: Ensure free items are permanently consolidated in DB 
            // before any deductions run to prevent duplicate deductions of old overlapping free quantities.
            $consolidatedData = collect($this->consolidateFreeItems($retailerOrder->items))->keyBy(function($i) {
                return is_array($i) ? ($i['id'] ?? null) : $i->id;
            });
            
            foreach ($retailerOrder->items as $orderItem) {
                if ($consolidated = $consolidatedData->get($orderItem->id)) {
                    $isArr = is_array($consolidated);
                    $newFreeQty = $isArr ? ($consolidated['free_quantity'] ?? 0) : ($consolidated->free_quantity ?? 0);
                    $newFreeSide = $isArr ? ($consolidated['free_side'] ?? null) : ($consolidated->free_side ?? null);
                    $newFreeSize = $isArr ? ($consolidated['free_size'] ?? null) : ($consolidated->free_size ?? null);
                    
                    if (
                        $orderItem->free_quantity != $newFreeQty ||
                        $orderItem->free_side != $newFreeSide ||
                        $orderItem->free_size != $newFreeSize
                    ) {
                        $orderItem->update([
                            'free_quantity' => $newFreeQty,
                            'free_side' => $newFreeSide,
                            'free_size' => $newFreeSize,
                        ]);
                    }
                }
            }
            $retailerOrder->load('items'); // Reload to reflect changes before proceeding
            // 1. Batch Allocation Logic
            $allocatedOrderItemIds = [];
            
            if ($itemsBatches) {
                // Validate allocations first
                $qtyErrors = [];
                foreach ($itemsBatches as $allocation) {
                    $orderItem = $retailerOrder->items()->findOrFail($allocation['order_item_id']);
                    $product = $orderItem->product;
                    $totalAllocated = 0;
                    foreach ($allocation['batches'] as $batchData) {
                        $totalAllocated += (float)$batchData['quantity'];
                    }
                    $pName = $product->product_name;
                    $vLabel = array_filter([$orderItem->side, $orderItem->size]);
                    if (!empty($vLabel)) {
                        $pName .= ' [' . implode('/', $vLabel) . ']';
                    }
                    
                    $expectedPaid = $orderItem->quantity;
                    
                    if ($totalAllocated < $expectedPaid) {
                        $qtyErrors[] = "Total allocated quantity ({$totalAllocated}) is less than the ordered paid quantity ({$expectedPaid}) for item: " . $pName;
                    }
                }

                if (!empty($qtyErrors)) {
                    throw new \Exception(implode("\n", $qtyErrors));
                }

                foreach ($itemsBatches as $allocation) {
                    $orderItem = $retailerOrder->items()->findOrFail($allocation['order_item_id']);
                    $allocatedOrderItemIds[] = $orderItem->id;
                    $product = $orderItem->product;
                    $multiplier = $this->convertQuantityToStrips($product, 1, $orderItem->unit);
                    $totalAllocated = 0;
                    
                    $totalPaidQtyStrips = $orderItem->quantity * $multiplier;
                    $currentlyAllocatedStrips = 0;
                    
                    if ($orderItem->batches) {
                        $orderItem->batches()->delete(); // Clear existing batches
                    }
                    foreach ($allocation['batches'] as $batchData) {
                        if ($currentlyAllocatedStrips >= $totalPaidQtyStrips) {
                            break; // We have already allocated the required PAID quantity. Ignore excess.
                        }
                        
                        $invId = isset($batchData['inventory_id']) ? str_replace(['"', "'"], '', $batchData['inventory_id']) : null;

                        $invQuery = \App\Models\Inventory::where('distributor_id', $distributor->id)
                            ->where('product_id', $product->id);

                        if (empty($orderItem->side)) {
                            $invQuery->where(function($q) {
                                $q->whereNull('side')->orWhere('side', '');
                            });
                        } else {
                            $invQuery->where('side', $orderItem->side);
                        }

                        if (empty($orderItem->size)) {
                            $invQuery->where(function($q) {
                                $q->whereNull('size')->orWhere('size', '');
                            });
                        } else {
                            $invQuery->where('size', $orderItem->size);
                        }

                        $baseInvQuery = clone $invQuery;

                        if ($invId) {
                            $inventory = $invQuery->findOrFail($invId);
                        } elseif (isset($batchData['batch_no'])) {
                            $inventory = $invQuery->where('batch_no', $batchData['batch_no'])->first();
                            if (!$inventory) {
                                throw new \Exception("Could not find batch '{$batchData['batch_no']}' in your inventory for {$product->product_name}");
                            }
                        } else {
                            throw new \Exception("Inventory ID or Batch Number is required for allocation of {$product->product_name}");
                        }

                        $deductQtyBase = $batchData['quantity'] * $multiplier;
                        
                        if ($currentlyAllocatedStrips + $deductQtyBase > $totalPaidQtyStrips) {
                            $deductQtyBase = $totalPaidQtyStrips - $currentlyAllocatedStrips;
                        }
                        
                        $remainingQtyToDeduct = $deductQtyBase;

                        // 1. First, attempt to deduct from explicitly selected batch
                        $takeFromPrimary = min($inventory->stock, $remainingQtyToDeduct);
                        
                        if ($takeFromPrimary > 0) {
                            DB::table('inventories')->where('id', $inventory->id)->decrement('stock', $takeFromPrimary);
                            
                            \App\Models\RetailerOrderItemBatch::create([
                                'retailer_order_item_id' => $orderItem->id,
                                'batch_no' => $inventory->batch_no,
                                'expiry_date' => $inventory->expiry_date,
                                'quantity' => $takeFromPrimary / $multiplier,
                            ]);
                            
                            $remainingQtyToDeduct -= $takeFromPrimary;
                        }

                        // 2. Cascade (spillover) to other available batches (FIFO)
                        if ($remainingQtyToDeduct > 0) {
                            $otherBatches = (clone $baseInvQuery)
                                ->where('id', '!=', $inventory->id)
                                ->where('stock', '>', 0)
                                ->orderBy('expiry_date', 'asc')
                                ->get();

                            foreach ($otherBatches as $otherBatch) {
                                if ($remainingQtyToDeduct <= 0) break;

                                $takeFromOther = min($otherBatch->stock, $remainingQtyToDeduct);
                                
                                DB::table('inventories')->where('id', $otherBatch->id)->decrement('stock', $takeFromOther);
                                
                                \App\Models\RetailerOrderItemBatch::create([
                                    'retailer_order_item_id' => $orderItem->id,
                                    'batch_no' => $otherBatch->batch_no,
                                    'expiry_date' => $otherBatch->expiry_date,
                                    'quantity' => $takeFromOther / $multiplier,
                                ]);
                                
                                $remainingQtyToDeduct -= $takeFromOther;
                            }

                            if ($remainingQtyToDeduct > 0) {
                                throw new \Exception("Insufficient total stock across all available batches for product {$product->product_name}. You are short by " . ($remainingQtyToDeduct / $multiplier) . " items.");
                            }
                        }

                        $totalAllocated += ($deductQtyBase / $multiplier);
                        $currentlyAllocatedStrips += $deductQtyBase;
                    }
                }
            }

            // 2. Auto-Deduct Free Quantities (and any unallocated items) via FEFO
            foreach ($retailerOrder->items as $orderItem) {
                $product = $orderItem->product;
                $multiplier = $this->convertQuantityToStrips($product, 1, $orderItem->unit);
                
                $isAllocated = in_array($orderItem->id, $allocatedOrderItemIds);
                $neededForAutoDeduction = $isAllocated ? $orderItem->free_quantity : ($orderItem->quantity + $orderItem->free_quantity);
                
                if ($neededForAutoDeduction <= 0) continue;

                $deductionTasks = [];

                if (!empty($orderItem->free_size) && preg_match('/^\d+\s+/', trim($orderItem->free_size))) {
                    $parts = array_map('trim', explode(',', $orderItem->free_size));
                    foreach ($parts as $part) {
                        if (preg_match('/^(\d+)\s+(.+)$/', $part, $matches)) {
                            $deductionTasks[] = [
                                'qty' => (int)$matches[1],
                                'side' => $orderItem->free_side ?: $orderItem->side,
                                'size' => trim($matches[2])
                            ];
                        } else {
                            $deductionTasks[] = [
                                'qty' => $orderItem->free_quantity, 
                                'side' => $orderItem->free_side ?: $orderItem->side,
                                'size' => $part
                            ];
                        }
                    }
                    
                    if (!$isAllocated && $orderItem->quantity > 0) {
                        $deductionTasks[] = [
                            'qty' => $orderItem->quantity,
                            'side' => $orderItem->side,
                            'size' => $orderItem->size
                        ];
                    }
                } else {
                    $deductionTasks[] = [
                        'qty' => $neededForAutoDeduction,
                        'side' => $isAllocated ? ($orderItem->free_side ?: $orderItem->side) : ($orderItem->side ?: $orderItem->free_side),
                        'size' => $isAllocated ? ($orderItem->free_size ?: $orderItem->size) : ($orderItem->size ?: $orderItem->free_size)
                    ];
                }

                foreach ($deductionTasks as $task) {
                    $neededStrips = $task['qty'] * $multiplier;
                    if ($neededStrips <= 0) continue;

                    $querySide = $task['side'];
                    $querySize = $task['size'];

                    $invQuery = \App\Models\Inventory::where('distributor_id', $distributor->id)
                        ->where('product_id', $product->id);

                    if (empty($querySide)) {
                        $invQuery->where(function($q) {
                            $q->whereNull('side')->orWhere('side', '');
                        });
                    } else {
                        $invQuery->where('side', $querySide);
                    }

                    if (empty($querySize)) {
                        $invQuery->where(function($q) {
                            $q->whereNull('size')->orWhere('size', '');
                        });
                    } else {
                        $invQuery->where('size', $querySize);
                    }

                    $inventories = $invQuery->where('stock', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->get();

                    if ($inventories->sum('stock') < $neededStrips) {
                        $variantInfo = array_filter([$querySide, $querySize]);
                        $vText = !empty($variantInfo) ? ' [' . implode('/', $variantInfo) . ']' : '';
                        throw new \Exception("Insufficient stock for free/unallocated item: {$product->product_name}{$vText}");
                    }

                    $remainingStrips = $neededStrips;
                    foreach ($inventories as $inv) {
                        if ($remainingStrips <= 0) break;
                        $takeStrips = min($inv->stock, $remainingStrips);
                        DB::table('inventories')
                            ->where('id', $inv->id)
                            ->decrement('stock', $takeStrips);

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

            // 3. Loyalty Points Calculation handled by RetailerOrderObserver

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

                // OneSignal Push
                $this->sendOneSignalPush(
                    [$retailerOrder->retailer->user->id],
                    "Your order #{$retailerOrder->order_code} has been accepted. Please confirm your order.",
                    ['order_id' => $retailerOrder->id, 'type' => 'retailer_order'],
                    'Order Approved'
                );
            }

            DB::commit();
            return [
                'success' => 'Order accepted successfully!',
                'loyalty_points_earned' => 0, // Handled by Observer
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

            // OneSignal Push
            $this->sendOneSignalPush(
                [$retailerOrder->retailer->user->id],
                "Your order #{$retailerOrder->order_code} has been rejected by the distributor.",
                ['order_id' => $retailerOrder->id, 'type' => 'retailer_order'],
                'Order Rejected'
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
