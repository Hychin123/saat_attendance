<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Set extends Model
{
    use HasFactory;

    protected $fillable = [
        'set_code',
        'set_name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Generate unique set code
     */
    public static function generateSetCode(): string
    {
        $lastSet = self::latest('id')->first();
        
        if ($lastSet && preg_match('/SET-(\d+)/', $lastSet->set_code, $matches)) {
            $lastNumber = intval($matches[1]);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "SET-{$newNumber}";
    }

    /**
     * Get the set items (pivot)
     */
    public function setItems(): HasMany
    {
        return $this->hasMany(SetItem::class);
    }

    /**
     * Get the items in this set
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'set_items')
            ->withPivot('quantity', 'unit')
            ->withTimestamps();
    }

    /**
     * Get set usages
     */
    public function usages(): HasMany
    {
        return $this->hasMany(SetUsage::class);
    }

    /**
     * Get total items count in set
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->setItems->sum('quantity');
    }

    /**
     * Check if set can be used (all items have sufficient stock)
     */
    public function canUse(int $setsQuantity, int $warehouseId): array
    {
        $insufficientItems = [];

        foreach ($this->setItems as $setItem) {
            $availableStock = Stock::where('item_id', $setItem->item_id)
                ->where('warehouse_id', $warehouseId)
                ->sum('quantity');

            $requiredQuantity = $setItem->quantity * $setsQuantity;

            if ($availableStock < $requiredQuantity) {
                $insufficientItems[] = [
                    'item' => $setItem->item->item_name,
                    'required' => $requiredQuantity,
                    'available' => $availableStock,
                    'shortage' => $requiredQuantity - $availableStock,
                ];
            }
        }

        return $insufficientItems;
    }
}
