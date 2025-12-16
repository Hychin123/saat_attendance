<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
        'age',
        'school',
        'role_id',
        'salary',
        'kpa',
        'phone',
        'profile_image',
        'telegram_chat_id',
        'telegram_notifications',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'salary' => 'decimal:2',
            'is_super_admin' => 'boolean',
            'telegram_notifications' => 'boolean',
        ];
    }

    /**
     * Get the role that owns the user.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the attendances for the user.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the shifts assigned to this user.
     */
    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'user_shifts')
            ->withPivot('effective_from', 'effective_to', 'is_primary')
            ->withTimestamps();
    }

    /**
     * Get the current active shift for the user on a given date.
     */
    public function getCurrentShift(?Carbon $date = null): ?Shift
    {
        $date = $date ?? now();
        
        return $this->shifts()
            ->wherePivot('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('user_shifts.effective_to')
                    ->orWherePivot('effective_to', '>=', $date->format('Y-m-d'));
            })
            ->wherePivot('is_primary', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Get all active shifts for the user on a given date.
     */
    public function getActiveShifts(?Carbon $date = null): \Illuminate\Database\Eloquent\Collection
    {
        $date = $date ?? now();
        
        return $this->shifts()
            ->wherePivot('effective_from', '<=', $date->format('Y-m-d'))
            ->where(function ($query) use ($date) {
                $query->whereNull('user_shifts.effective_to')
                    ->orWherePivot('effective_to', '>=', $date->format('Y-m-d'));
            })
            ->where('is_active', true)
            ->get();
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission, string $resource): bool
    {
        // Super admin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if user's role has the permission
        return $this->role?->hasPermission($permission, $resource) ?? false;
    }

    /**
     * Check if user can access the Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true; // You can add custom logic here
    }

    /**
     * Get the user's name for Filament.
     */
    public function getFilamentName(): string
    {
        return $this->name ?? $this->email;
    }

    /**
     * Get the user's avatar URL for Filament.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return $this->profile_image ? Storage::url($this->profile_image) : null;
    }
}
