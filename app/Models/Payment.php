<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'sale_id',
        'amount',
        'payment_type',
        'payment_method',
        'paid_by',
        'transaction_reference',
        'notes',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // Payment type constants
    public const TYPE_DEPOSIT = 'DEPOSIT';
    public const TYPE_BALANCE = 'BALANCE';
    public const TYPE_FULL = 'FULL';

    // Payment method constants
    public const METHOD_CASH = 'CASH';
    public const METHOD_BANK = 'BANK';
    public const METHOD_QR = 'QR';
    public const METHOD_CREDIT_CARD = 'CREDIT_CARD';
    public const METHOD_OTHER = 'OTHER';

    public static function getPaymentTypes(): array
    {
        return [
            self::TYPE_DEPOSIT => 'Deposit',
            self::TYPE_BALANCE => 'Balance',
            self::TYPE_FULL => 'Full Payment',
        ];
    }

    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK => 'Bank Transfer',
            self::METHOD_QR => 'QR Code',
            self::METHOD_CREDIT_CARD => 'Credit Card',
            self::METHOD_OTHER => 'Other',
        ];
    }

    /**
     * Relationships
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'sale_id');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Scopes
     */
    public function scopeDeposits($query)
    {
        return $query->where('payment_type', self::TYPE_DEPOSIT);
    }

    public function scopeBalances($query)
    {
        return $query->where('payment_type', self::TYPE_BALANCE);
    }

    public function scopeBySale($query, $saleId)
    {
        return $query->where('sale_id', $saleId);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }
}
