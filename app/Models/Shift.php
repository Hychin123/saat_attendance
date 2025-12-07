<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;

class Shift extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'minimum_work_hours',
        'is_active',
        'is_overnight',
        'description',
        'working_days',
        'color',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_overnight' => 'boolean',
        'working_days' => 'array',
        'grace_period_minutes' => 'integer',
        'minimum_work_hours' => 'integer',
    ];

    /**
     * Get the users assigned to this shift.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_shifts')
            ->withPivot('effective_from', 'effective_to', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Check if user is late based on check-in time
     */
    public function isLate(Carbon $checkInTime): bool
    {
        $shiftStart = Carbon::parse($this->start_time);
        $gracePeriod = $shiftStart->copy()->addMinutes($this->grace_period_minutes);
        
        return $checkInTime->gt($gracePeriod);
    }

    /**
     * Calculate late minutes
     */
    public function getLateMinutes(Carbon $checkInTime): int
    {
        $shiftStart = Carbon::parse($this->start_time);
        $gracePeriod = $shiftStart->copy()->addMinutes($this->grace_period_minutes);
        
        if ($checkInTime->lte($gracePeriod)) {
            return 0;
        }
        
        return $checkInTime->diffInMinutes($shiftStart);
    }

    /**
     * Check if shift is active on a given day
     */
    public function isActiveOnDay(string $dayName): bool
    {
        if (empty($this->working_days)) {
            return true; // If no specific days set, active all days
        }
        
        return in_array(strtolower($dayName), array_map('strtolower', $this->working_days));
    }

    /**
     * Get shift duration in hours
     */
    public function getDurationInHours(): float
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        
        if ($this->is_overnight) {
            $end->addDay();
        }
        
        return $start->diffInHours($end, true);
    }

    /**
     * Scope to get only active shifts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
