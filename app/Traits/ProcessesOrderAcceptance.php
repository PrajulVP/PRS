<?php

namespace App\Traits;

use App\Models\RetailerOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait ProcessesOrderAcceptance
{
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
}
