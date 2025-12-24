<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'set_id',
        'item_id',
        'quantity',
        'unit',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    /**
     * Get the set
     */
    public function set(): BelongsTo
    {
        return $this->belongsTo(Set::class);
    }

    /**
     * Get the item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
