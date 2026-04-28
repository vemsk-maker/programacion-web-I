<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\MovementType;
use App\Models\Batch;
use App\Models\Document;
use App\Models\InventoryMovement;
use App\Models\MovementGroup;
use App\Models\StockCache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    // =========================================================================
    // 1. PURCHASE
    // Expected $data shape:
    // [
    //   'user_id'             => int,
    //   'location_id'         => int,   // destination (warehouse)
    //   'supplier_id'         => int|null,
    //   'reference_doc'       => string|null,
    //   'notes'               => string|null,
    //   'lines' => [
    //     [
    //       'product_id'      => int,
    //       'batch_code'      => string|null,
    //       'expiration_date' => string|null,
    //       'quantity'        => float,
    //       'unit_cost'       => float|null,
    //     ], ...
    //   ],
    // ]
    // =========================================================================
    public function registerPurchase(array $data): MovementGroup
    {
        return DB::transaction(function () use ($data) {
            $group = MovementGroup::create([
                'type'               => MovementType::Purchase,
                'reference_doc'      => $data['reference_doc'] ?? null,
                'user_id'            => $data['user_id'],
                'origin_location_id' => null,
                'notes'              => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                $batch = null;

                if (! empty($line['batch_code'])) {
                    $batch = Batch::firstOrCreate(
                        [
                            'product_id' => $line['product_id'],
                            'batch_code' => $line['batch_code'],
                        ],
                        [
                            'supplier_id'     => $data['supplier_id'] ?? null,
                            'expiration_date' => $line['expiration_date'] ?? null,
                            'notes'           => $line['notes'] ?? null,
                        ]
                    );
                }

                InventoryMovement::create([
                    'group_id'         => $group->id,
                    'product_id'       => $line['product_id'],
                    'batch_id'         => $batch?->id,
                    'from_location_id' => null,
                    'to_location_id'   => $data['location_id'],
                    'quantity'         => $line['quantity'],
                    'unit_cost'        => $line['unit_cost'] ?? null,
                ]);
            }

            return $group->load('movements');
        });
    }

    // =========================================================================
    // 2. SALE
    // Expected $data shape:
    // [
    //   'user_id'       => int,
    //   'location_id'   => int,   // source (store/warehouse)
    //   'reference_doc' => string|null,
    //   'notes'         => string|null,
    //   'lines' => [
    //     [
    //       'product_id' => int,
    //       'batch_id'   => int|null,  // ignored when use_batches=true (FEFO applied)
    //       'quantity'   => float,
    //       'unit_cost'  => float|null,
    //     ], ...
    //   ],
    // ]
    // =========================================================================
    public function registerSale(array $data): MovementGroup
    {
        return DB::transaction(function () use ($data) {
            $group = MovementGroup::create([
                'type'               => MovementType::Sale,
                'reference_doc'      => $data['reference_doc'] ?? null,
                'user_id'            => $data['user_id'],
                'origin_location_id' => $data['location_id'],
                'notes'              => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                $product  = \App\Models\Product::findOrFail($line['product_id']);
                $batchId  = null;
                $quantity = (float) $line['quantity'];

                if ($product->use_batches) {
                    // FEFO: oldest expiration_date first, only from this location
                    $batchId = $this->resolveFefoBatch(
                        $line['product_id'],
                        $data['location_id'],
                        $quantity
                    );
                } else {
                    $batchId = $line['batch_id'] ?? null;
                }

                InventoryMovement::create([
                    'group_id'         => $group->id,
                    'product_id'       => $line['product_id'],
                    'batch_id'         => $batchId,
                    'from_location_id' => $data['location_id'],
                    'to_location_id'   => null,
                    'quantity'         => $quantity,
                    'unit_cost'        => $line['unit_cost'] ?? null,
                ]);
            }

            return $group->load('movements');
        });
    }

    // =========================================================================
    // 3. TRANSFER
    // Expected $data shape:
    // [
    //   'user_id'              => int,
    //   'origin_location_id'   => int,
    //   'dest_location_id'     => int,
    //   'reference_doc'        => string|null,
    //   'notes'                => string|null,
    //   'lines' => [
    //     [
    //       'product_id' => int,
    //       'batch_id'   => int|null,
    //       'quantity'   => float,
    //       'unit_cost'  => float|null,
    //     ], ...
    //   ],
    // ]
    // =========================================================================
    public function registerTransfer(array $data): MovementGroup
    {
        return DB::transaction(function () use ($data) {
            $group = MovementGroup::create([
                'type'               => MovementType::Transfer,
                'reference_doc'      => $data['reference_doc'] ?? null,
                'user_id'            => $data['user_id'],
                'origin_location_id' => $data['origin_location_id'],
                'notes'              => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                $quantity = (float) $line['quantity'];
                $batchId  = $line['batch_id'] ?? null;
                $unitCost = $line['unit_cost'] ?? null;

                // Movement 1 – out of origin
                InventoryMovement::create([
                    'group_id'         => $group->id,
                    'product_id'       => $line['product_id'],
                    'batch_id'         => $batchId,
                    'from_location_id' => $data['origin_location_id'],
                    'to_location_id'   => null,
                    'quantity'         => $quantity,
                    'unit_cost'        => $unitCost,
                ]);

                // Movement 2 – into destination
                InventoryMovement::create([
                    'group_id'         => $group->id,
                    'product_id'       => $line['product_id'],
                    'batch_id'         => $batchId,
                    'from_location_id' => null,
                    'to_location_id'   => $data['dest_location_id'],
                    'quantity'         => $quantity,
                    'unit_cost'        => $unitCost,
                ]);
            }

            return $group->load('movements');
        });
    }

    // =========================================================================
    // 4. ADJUSTMENT
    // Expected $data shape:
    // [
    //   'user_id'     => int,
    //   'location_id' => int,
    //   'reference_doc' => string|null,
    //   'notes'       => string|null,
    //   'lines' => [
    //     [
    //       'product_id' => int,
    //       'batch_id'   => int|null,
    //       'quantity'   => float,  // positive = add stock, negative = remove stock
    //       'unit_cost'  => float|null,
    //     ], ...
    //   ],
    // ]
    // =========================================================================
    public function registerAdjustment(array $data): MovementGroup
    {
        return DB::transaction(function () use ($data) {
            // Validate sufficient stock for negative adjustments before writing
            foreach ($data['lines'] as $line) {
                $quantity = (float) $line['quantity'];

                if ($quantity < 0) {
                    $available = $this->getStock(
                        $data['location_id'],
                        $line['product_id'],
                        $line['batch_id'] ?? null
                    );

                    if ($available + $quantity < 0) {
                        throw new RuntimeException(
                            "Insufficient stock for adjustment: product_id={$line['product_id']}, " .
                            "available={$available}, requested=" . abs($quantity)
                        );
                    }
                }
            }

            $group = MovementGroup::create([
                'type'               => MovementType::Adjustment,
                'reference_doc'      => $data['reference_doc'] ?? null,
                'user_id'            => $data['user_id'],
                'origin_location_id' => $data['location_id'],
                'notes'              => $data['notes'] ?? null,
            ]);

            foreach ($data['lines'] as $line) {
                $quantity = (float) $line['quantity'];

                if ($quantity >= 0) {
                    $fromLocation = null;
                    $toLocation   = $data['location_id'];
                } else {
                    $fromLocation = $data['location_id'];
                    $toLocation   = null;
                    $quantity     = abs($quantity);
                }

                InventoryMovement::create([
                    'group_id'         => $group->id,
                    'product_id'       => $line['product_id'],
                    'batch_id'         => $line['batch_id'] ?? null,
                    'from_location_id' => $fromLocation,
                    'to_location_id'   => $toLocation,
                    'quantity'         => $quantity,
                    'unit_cost'        => $line['unit_cost'] ?? null,
                ]);
            }

            return $group->load('movements');
        });
    }

    // =========================================================================
    // 5. CANCEL MOVEMENT GROUP (only sales)
    // =========================================================================
    public function cancelMovementGroup(int $groupId, int $userId): MovementGroup
    {
        return DB::transaction(function () use ($groupId, $userId) {
            $original = MovementGroup::with(['movements', 'document'])->findOrFail($groupId);

            if ($original->type !== MovementType::Sale) {
                throw new RuntimeException(
                    "Only sale movement groups can be cancelled. Group #{$groupId} is of type '{$original->type->value}'."
                );
            }

            // Create a reversal group
            $reversal = MovementGroup::create([
                'type'               => MovementType::Adjustment,
                'reference_doc'      => 'CANCEL-' . $original->id,
                'user_id'            => $userId,
                'origin_location_id' => $original->origin_location_id,
                'notes'              => "Cancellation of movement group #{$original->id}",
            ]);

            // Insert inverse movements (return stock to origin)
            foreach ($original->movements as $movement) {
                InventoryMovement::create([
                    'group_id'         => $reversal->id,
                    'product_id'       => $movement->product_id,
                    'batch_id'         => $movement->batch_id,
                    'from_location_id' => $movement->to_location_id,   // inverted
                    'to_location_id'   => $movement->from_location_id, // inverted
                    'quantity'         => $movement->quantity,
                    'unit_cost'        => $movement->unit_cost,
                ]);
            }

            // Mark the associated document as cancelled if present
            if ($original->document) {
                $original->document->update(['status' => DocumentStatus::Cancelled]);
            }

            return $original->refresh();
        });
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    /**
     * FEFO: returns the batch_id with the oldest (non-null first, then earliest)
     * expiration_date that has available stock for the given product/location.
     * Throws if no batch with sufficient stock is found.
     */
    private function resolveFefoBatch(int $productId, int $locationId, float $quantity): int
    {
        $batch = StockCache::query()
            ->join('batches', 'batches.id', '=', 'stock_cache.batch_id')
            ->where('stock_cache.location_id', $locationId)
            ->where('stock_cache.product_id', $productId)
            ->where('stock_cache.quantity', '>=', $quantity)
            ->whereNotNull('stock_cache.batch_id')
            ->orderByRaw('batches.expiration_date ASC NULLS FIRST')
            ->select('stock_cache.batch_id')
            ->first();

        if (! $batch) {
            throw new RuntimeException(
                "No batch with sufficient stock (FEFO) found for product_id={$productId} " .
                "at location_id={$locationId} (requested {$quantity})."
            );
        }

        return $batch->batch_id;
    }

    /**
     * Returns current quantity in stock_cache for a given location/product/batch.
     */
    private function getStock(int $locationId, int $productId, ?int $batchId): float
    {
        $query = StockCache::where('location_id', $locationId)
            ->where('product_id', $productId);

        if ($batchId !== null) {
            $query->where('batch_id', $batchId);
        } else {
            $query->whereNull('batch_id');
        }

        return (float) ($query->value('quantity') ?? 0);
    }
}
