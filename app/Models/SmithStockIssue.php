<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmithStockIssue extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'user_id',
        'warehouse_id',
        'item_id',
        'quantity',
        'issue_date',
        'project_name',
        'purpose',
        'notes',
        'status',
        'issued_by',
        'issued_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'issued_at' => 'datetime',
        'quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($smithStockIssue) {
            if (!$smithStockIssue->reference_no) {
                $smithStockIssue->reference_no = self::generateReferenceNo();
            }
        });
    }

    public static function generateReferenceNo(): string
    {
        $year = date('Y');
        $lastRecord = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastRecord ? ((int) substr($lastRecord->reference_no, -3)) + 1 : 1;
        return 'SSI-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
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

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
