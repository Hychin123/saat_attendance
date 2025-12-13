<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmithReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'user_id',
        'warehouse_id',
        'item_id',
        'quantity',
        'replacement_item_id',
        'replacement_quantity',
        'return_date',
        'return_reason',
        'description',
        'notes',
        'status',
        'approved_by',
        'approved_at',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'return_date' => 'date',
        'approved_at' => 'datetime',
        'processed_at' => 'datetime',
        'quantity' => 'decimal:2',
        'replacement_quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($smithReturn) {
            if (!$smithReturn->reference_no) {
                $smithReturn->reference_no = self::generateReferenceNo();
            }
        });
    }

    public static function generateReferenceNo(): string
    {
        $year = date('Y');
        $lastRecord = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastRecord ? ((int) substr($lastRecord->reference_no, -3)) + 1 : 1;
        return 'SR-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
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

    public function replacementItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'replacement_item_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
