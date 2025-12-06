<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'warehouse_id',
        'customer_department',
        'dispatch_date',
        'approved_by',
        'issued_by',
        'status',
        'reason',
        'notes',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($stockOut) {
            if (!$stockOut->reference_no) {
                $stockOut->reference_no = self::generateReferenceNo();
            }
        });
    }

    public static function generateReferenceNo(): string
    {
        $year = date('Y');
        $lastRecord = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastRecord ? ((int) substr($lastRecord->reference_no, -3)) + 1 : 1;
        return 'SO-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function issuedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOutItem::class);
    }
}
