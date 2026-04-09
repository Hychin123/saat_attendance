<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'role_id',
        'shift_id',
        'date',
        'time_in',
        'time_out',
        'notes',
        'check_in_ip',
        'check_out_ip',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_device',
        'check_out_device',
        'is_late',
        'late_minutes',
        'work_hours',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime',
        'time_out' => 'datetime',
        'is_late' => 'boolean',
        'late_minutes' => 'integer',
        'work_hours' => 'decimal:2',
    ];

    /**
     * Get the user that owns the attendance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role that owns the attendance.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the shift that owns the attendance.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Scope a query to only include today's attendances.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Scope a query to only include attendances by date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope a query to only include attendances by role.
     */
    public function scopeByRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Check if user has checked in today.
     */
    public static function hasCheckedInToday($userId)
    {
        return static::where('user_id', $userId)
            ->whereDate('date', Carbon::today())
            ->exists();
    }

    /**
     * Check if user has checked out today.
     */
    public static function hasCheckedOutToday($userId)
    {
        return static::where('user_id', $userId)
            ->whereDate('date', Carbon::today())
            ->whereNotNull('time_out')
            ->exists();
    }

    /**
     * Get today's attendance for a user.
     */
    public static function getTodayAttendance($userId)
    {
        return static::where('user_id', $userId)
            ->whereDate('date', Carbon::today())
            ->first();
    }

    /**
     * Calculate work hours between time_in and time_out
     */
    public function calculateWorkHours(): ?float
    {
        if (!$this->time_in || !$this->time_out) {
            return null;
        }

        return $this->time_in->diffInHours($this->time_out, true);
    }

    /**
     * Check if attendance is late based on shift
     */
    public function checkIfLate(): array
    {
        if (!$this->shift || !$this->time_in) {
            return ['is_late' => false, 'late_minutes' => 0];
        }

        $isLate = $this->shift->isLate($this->time_in);
        $lateMinutes = $isLate ? $this->shift->getLateMinutes($this->time_in) : 0;

        return [
            'is_late' => $isLate,
            'late_minutes' => $lateMinutes,
        ];
    }

    /**
     * Boot method to auto-calculate work hours and late status
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($attendance) {
            // Calculate work hours if both time_in and time_out exist
            if ($attendance->time_in && $attendance->time_out) {
                $attendance->work_hours = $attendance->calculateWorkHours();
            }

            // Calculate late status if shift exists
            if ($attendance->shift_id && $attendance->time_in) {
                $lateInfo = $attendance->checkIfLate();
                $attendance->is_late = $lateInfo['is_late'];
                $attendance->late_minutes = $lateInfo['late_minutes'];
            }
        });
    }
}
