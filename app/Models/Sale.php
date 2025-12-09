<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sales';
    protected $primaryKey = 'sale_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sale_id',
        'customer_id',
        'agent_id',
        'warehouse_id',
        'total_amount',
        'discount',
        'tax',
        'net_total',
        'deposit_amount',
        'remaining_amount',
        'expected_ready_date',
        'completed_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_total' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'expected_ready_date' => 'date',
        'completed_date' => 'date',
    ];

    // Status constants
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_DEPOSITED = 'DEPOSITED';
    public const STATUS_PROCESSING = 'PROCESSING';
    public const STATUS_READY = 'READY';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_REFUNDED = 'REFUNDED';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_DEPOSITED => 'Deposited',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_READY => 'Ready',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_REFUNDED => 'Refunded',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (empty($sale->sale_id)) {
                $sale->sale_id = self::generateSaleId();
            }
        });
    }

    public static function generateSaleId(): string
    {
        $year = date('Y');
        $lastSale = self::withTrashed()
            ->where('sale_id', 'LIKE', "SAL-{$year}-%")
            ->orderBy('sale_id', 'desc')
            ->first();

        if ($lastSale) {
            $lastNumber = (int) substr($lastSale->sale_id, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "SAL-{$year}-{$newNumber}";
    }

    /**
     * Relationships
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id', 'sale_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'sale_id', 'sale_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'sale_id', 'sale_id');
    }

    /**
     * Helper methods
     */
    public function calculateTotals(): void
    {
        $this->total_amount = $this->items()->sum(DB::raw('quantity * unit_price'));
        $this->net_total = $this->total_amount - $this->discount + $this->tax;
        $this->remaining_amount = $this->net_total - $this->deposit_amount;
    }

    public function isFullyPaid(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function canProcess(): bool
    {
        return $this->status === self::STATUS_DEPOSITED && $this->deposit_amount > 0;
    }

    public function canComplete(): bool
    {
        return $this->status === self::STATUS_READY && $this->isFullyPaid();
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDeposited($query)
    {
        return $query->where('status', self::STATUS_DEPOSITED);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}
