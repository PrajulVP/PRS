<?php

namespace App\Traits;

trait ConsolidatesFreeItems
{
    /**
     * Consolidate free quantities and sizes for the same product into the first array item.
     * 
     * @param array|\Illuminate\Support\Collection $items
     * @return array
     */
    public function consolidateFreeItems($items)
    {
        $itemsArray = is_array($items) ? $items : $items->toArray();
        $formatted = [];
        
        $byProduct = [];
        foreach ($itemsArray as $item) {
            $pid = $item['product_id'] ?? null;
            if ($pid) {
                $byProduct[$pid][] = $item;
            } else {
                $formatted[] = $item; // Pass-through if no product_id
            }
        }

        foreach ($byProduct as $productId => $groupItems) {
            $isFirst = true;
            $totalFreeQty = 0;
            $allFreeSides = [];
            $allFreeSizes = [];

            // Pass 1: Aggregate
            foreach ($groupItems as $item) {
                $totalFreeQty += (int)($item['free_quantity'] ?? 0);
                
                if (!empty($item['free_side'])) {
                    $sides = explode(',', $item['free_side']);
                    foreach ($sides as $s) {
                        $allFreeSides[] = trim($s);
                    }
                }
                if (!empty($item['free_size'])) {
                    $sizes = explode(',', $item['free_size']);
                    foreach ($sizes as $s) {
                        $allFreeSizes[] = trim($s);
                    }
                }
            }

            // Merge helper
            $mergeVariants = function($arr) {
                if (empty($arr)) return ['string' => null, 'total' => 0];
                $counts = [];
                $total = 0;
                foreach ($arr as $str) {
                    if (preg_match('/(\d+)\s+([A-Za-z0-9]+)/', $str, $matches)) {
                        $qty = (int)$matches[1];
                        $var = strtoupper($matches[2]);
                        $counts[$var] = ($counts[$var] ?? 0) + $qty;
                        $total += $qty;
                    } else {
                        $var = strtoupper(trim($str));
                        $counts[$var] = ($counts[$var] ?? 0) + 1;
                        $total += 1;
                    }
                }
                $res = [];
                foreach ($counts as $var => $qty) {
                    $res[] = "$qty $var";
                }
                return [
                    'string' => empty($res) ? null : implode(', ', $res),
                    'total' => $total
                ];
            };

            $sideData = $mergeVariants($allFreeSides);
            $sizeData = $mergeVariants($allFreeSizes);

            $finalFreeSide = $sideData['string'];
            $finalFreeSize = $sizeData['string'];
            
            // The totalFreeQty is already the sum of all free quantities from all rows.
            // We do not overwrite it with variantQty because some free items might not have a size assigned yet.

            // Pass 2: Redistribute
            foreach ($groupItems as $item) {
                if ($isFirst) {
                    $item['free_quantity'] = $totalFreeQty;
                    $item['free_side'] = $finalFreeSide;
                    $item['free_size'] = $finalFreeSize;
                    $isFirst = false;
                } else {
                    $item['free_quantity'] = 0;
                    $item['free_side'] = null;
                    $item['free_size'] = null;
                }
                $formatted[] = $item;
            }
        }

        return $formatted;
    }
}
