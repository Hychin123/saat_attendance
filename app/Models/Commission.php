<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use HasFactory;

    protected $table = 'commissions';
    protected $primaryKey = 'commission_id';

    protected $fillable = [
        'sale_id',
        'agent_id',
        'commission_rate',
        'total_sale_amount',
        'commission_amount',
        'status',
        'paid_date',
        'payment_reference',
        'notes',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'total_sale_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'paid_date' => 'date',
    ];

    // Status constants
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PAID = 'PAID';
    public const STATUS_CANCELLED = 'CANCELLED';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PAID => 'Paid',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($commission) {
            if (empty($commission->commission_amount)) {
                $commission->calculateCommission();
            }
        });
    }

    /**
     * Relationships
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'sale_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Helper methods
     */
    public function calculateCommission(): void
    {
        if ($this->total_sale_amount && $this->commission_rate) {
            $this->commission_amount = ($this->total_sale_amount * $this->commission_rate) / 100;
        }
    }

    public function markAsPaid(string $paymentReference = null): void
    {
        $this->status = self::STATUS_PAID;
        $this->paid_date = now();
        $this->payment_reference = $paymentReference;
        $this->save();
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
}
