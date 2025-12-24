<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'set_id',
        'warehouse_id',
        'user_id',
        'quantity',
        'usage_date',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'quantity' => 'integer',
    ];

    /**
     * Get the set
     */
    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    /**
     * Get the warehouse
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the user who used the set
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot method to auto-deduct stock when set is used
     */
    protected static function booted()
    {
        static::created(function (SetUsage $setUsage) {
            $setUsage->deductStock();
        });
    }

    /**
     * Deduct stock for all items in the set
     */
    public function deductStock(): void
    {
        $set = $this->set()->with('setItems.item')->first();

        foreach ($set->setItems as $setItem) {
            $totalQuantityToDeduct = $setItem->quantity * $this->quantity;

            // Create stock out record
            StockOut::create([
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $setItem->item_id,
                'quantity' => $totalQuantityToDeduct,
                'stock_out_date' => $this->usage_date,
                'reason' => "Set Usage: {$set->set_name} (x{$this->quantity})",
                'notes' => $this->purpose,
                'status' => 'completed',
            ]);

            // Deduct from stock
            $stock = Stock::where('warehouse_id', $this->warehouse_id)
                ->where('item_id', $setItem->item_id)
                ->first();

            if ($stock) {
                $stock->decrement('quantity', $totalQuantityToDeduct);
            }

            // Record stock movement
            StockMovement::create([
                'warehouse_id' => $this->warehouse_id,
                'item_id' => $setItem->item_id,
                'movement_type' => 'OUT',
                'quantity' => $totalQuantityToDeduct,
                'movement_date' => $this->usage_date,
                'reference_type' => 'SetUsage',
                'reference_id' => $this->id,
                'notes' => "Set: {$set->set_name} - {$setItem->item->item_name} (x{$this->quantity} sets)",
            ]);
        }
    }
}
