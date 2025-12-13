<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'user_id',
        'warehouse_id',
        'item_id',
        'adjustment_type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'adjustment_date',
        'reason',
        'notes',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'approved_at' => 'datetime',
        'quantity' => 'decimal:2',
        'previous_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($materialAdjustment) {
            if (!$materialAdjustment->reference_no) {
                $materialAdjustment->reference_no = self::generateReferenceNo();
            }
        });
    }

    public static function generateReferenceNo(): string
    {
        $year = date('Y');
        $lastRecord = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastRecord ? ((int) substr($lastRecord->reference_no, -3)) + 1 : 1;
        return 'MA-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
