<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'warehouse_id',
        'location_id',
        'item_id',
        'adjustment_type',
        'quantity',
        'batch_number',
        'adjustment_date',
        'reason',
        'adjusted_by',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stockAdjustment) {
            if (!$stockAdjustment->reference_no) {
                $stockAdjustment->reference_no = self::generateReferenceNo();
            }
        });
    }

    public static function generateReferenceNo(): string
    {
        $year = date('Y');
        $lastRecord = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastRecord ? ((int) substr($lastRecord->reference_no, -3)) + 1 : 1;
        return 'SA-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function adjustedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
