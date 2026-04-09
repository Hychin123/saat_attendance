<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MachineWaterUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'liters_dispensed',
        'usage_date',
        'notes',
    ];

    protected $casts = [
        'liters_dispensed' => 'integer',
        'usage_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    /**
     * Scopes
     */
    public function scopeByMachine($query, $machineId)
    {
        return $query->where('machine_id', $machineId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('usage_date', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('usage_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('usage_date', now()->month)
                     ->whereYear('usage_date', now()->year);
    }
}
