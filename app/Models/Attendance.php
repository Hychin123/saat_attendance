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
}
