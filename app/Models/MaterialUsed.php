<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialUsed extends Model
{
    use HasFactory;

    protected $table = 'material_used';

    protected $fillable = [
        'reference_no',
        'user_id',
        'warehouse_id',
        'item_id',
        'quantity',
        'unit',
        'usage_date',
        'project_name',
        'purpose',
        'notes',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'approved_at' => 'datetime',
        'quantity' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($materialUsed) {
            if (!$materialUsed->reference_no) {
                $materialUsed->reference_no = self::generateReferenceNo();
            }
        });
    }

    public static function generateReferenceNo(): string
    {
        $year = date('Y');
        $lastRecord = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastRecord ? ((int) substr($lastRecord->reference_no, -3)) + 1 : 1;
        return 'MU-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
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
