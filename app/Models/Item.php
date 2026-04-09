<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code',
        'item_name',
        'description',
        'category_id',
        'brand_id',
        'unit',
        'barcode',
        'cost_price',
        'selling_price',
        'has_expiry',
        'reorder_level',
        'image',
        'is_active',
    ];

    protected $casts = [
        'has_expiry' => 'boolean',
        'is_active' => 'boolean',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->item_code) {
                $item->item_code = self::generateItemCode();
            }
        });
    }

    public static function generateItemCode(): string
    {
        $lastItem = self::orderBy('id', 'desc')->first();
        $number = $lastItem ? $lastItem->id + 1 : 1;
        return 'ITM-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->stocks()->sum('quantity');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->total_stock <= $this->reorder_level;
    }
}
