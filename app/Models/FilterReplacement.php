<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FilterReplacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_filter_id',
        'replaced_date',
        'replaced_by',
        'old_used_liters',
        'days_used',
        'note',
    ];

    protected $casts = [
        'replaced_date' => 'date',
        'old_used_liters' => 'integer',
        'days_used' => 'integer',
    ];

    /**
     * Relationships
     */
    public function machineFilter(): BelongsTo
    {
        return $this->belongsTo(MachineFilter::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replaced_by');
    }

    /**
     * Get the machine through the machine filter
     */
    public function getMachineAttribute()
    {
        return $this->machineFilter->machine;
    }

    /**
     * Get the filter through the machine filter
     */
    public function getFilterAttribute()
    {
        return $this->machineFilter->filter;
    }

    /**
     * Scopes
     */
    public function scopeByTechnician($query, $technicianId)
    {
        return $query->where('replaced_by', $technicianId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('replaced_date', '>=', now()->subDays($days));
    }
}
