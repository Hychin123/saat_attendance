<?php

namespace App\Observers;

use App\Models\SmithStockIssue;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class SmithStockIssueObserver
{
    /**
     * Handle the SmithStockIssue "updated" event.
     */
    public function updated(SmithStockIssue $smithStockIssue): void
    {
        // If status changed to issued, reduce stock
        if ($smithStockIssue->isDirty('status')) {
            $oldStatus = $smithStockIssue->getOriginal('status');
            $newStatus = $smithStockIssue->status;

            if ($newStatus === 'issued' && $oldStatus !== 'issued') {
                $this->issueStock($smithStockIssue);
                
                // Set issue info
                if (!$smithStockIssue->issued_by) {
                    $smithStockIssue->setAttribute('issued_by', auth()->id());
                    $smithStockIssue->setAttribute('issued_at', now());
                    $smithStockIssue->saveQuietly();
                }
            }
        }
    }

    /**
     * Handle the SmithStockIssue "deleted" event.
     */
    public function deleted(SmithStockIssue $smithStockIssue): void
    {
        // Restore stock if issue was completed
        if ($smithStockIssue->status === 'issued') {
            $this->restoreStock($smithStockIssue);
        }
    }

    /**
     * Issue stock to smith
     */
    protected function issueStock(SmithStockIssue $smithStockIssue): void
    {
        DB::transaction(function () use ($smithStockIssue) {
            // Find stock record
            $stock = \App\Models\Stock::where('item_id', $smithStockIssue->item_id)
                ->where('warehouse_id', $smithStockIssue->warehouse_id)
                ->first();

            if (!$stock) {
                throw new \Exception("No stock found for this item in the warehouse.");
            }

            if ($stock->quantity < $smithStockIssue->quantity) {
                throw new \Exception("Insufficient stock. Available: {$stock->quantity}, Required: {$smithStockIssue->quantity}");
            }

            // Reduce stock
            $stock->decrement('quantity', (float) $smithStockIssue->quantity);

            // Create stock movement record
            StockMovement::create([
                'item_id' => $smithStockIssue->item_id,
                'warehouse_id' => $smithStockIssue->warehouse_id,
                'location_id' => $stock->location_id,
                'movement_type' => 'out',
                'quantity' => $smithStockIssue->quantity,
                'reference_type' => 'smith_stock_issue',
                'reference_id' => $smithStockIssue->id,
                'notes' => "Stock issued to {$smithStockIssue->user->name}. Project: {$smithStockIssue->project_name}",
            ]);
        });
    }

    /**
     * Restore stock when issue is cancelled/deleted
     */
    protected function restoreStock(SmithStockIssue $smithStockIssue): void
    {
        DB::transaction(function () use ($smithStockIssue) {
            // Find stock record
            $stock = \App\Models\Stock::where('item_id', $smithStockIssue->item_id)
                ->where('warehouse_id', $smithStockIssue->warehouse_id)
                ->first();

            if ($stock) {
                // Add stock back
                $stock->increment('quantity', (float) $smithStockIssue->quantity);

                // Create stock movement record
                StockMovement::create([
                    'item_id' => $smithStockIssue->item_id,
                    'warehouse_id' => $smithStockIssue->warehouse_id,
                    'location_id' => $stock->location_id,
                    'movement_type' => 'in',
                    'quantity' => $smithStockIssue->quantity,
                    'reference_type' => 'smith_stock_issue_cancelled',
                    'reference_id' => $smithStockIssue->id,
                    'notes' => "Stock issue cancelled/deleted - stock restored",
                ]);
            }
        });
    }
}
