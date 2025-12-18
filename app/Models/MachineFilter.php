<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class MachineFilter extends Model
{
    use HasFactory;

    protected $fillable = [
        'machine_id',
        'filter_id',
        'install_date',
        'used_liters',
        'status',
        'notes',
    ];

    protected $casts = [
        'install_date' => 'date',
        'used_liters' => 'integer',
    ];

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_NEED_CHANGE = 'need_change';
    public const STATUS_CHANGED = 'changed';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_NEED_CHANGE => 'Need Change',
            self::STATUS_CHANGED => 'Changed',
        ];
    }

    /**
     * Relationships
     */
    public function machine(): BelongsTo
    {
        return $this->belongsTo(Machine::class);
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }

    public function replacements(): HasMany
    {
        return $this->hasMany(FilterReplacement::class);
    }

    /**
     * Helper methods
     */
    public function getDaysUsed(): int
    {
        return Carbon::parse($this->install_date)->diffInDays(now());
    }

    public function getRemainingLiters(): ?int
    {
        if (!$this->filter->max_liters) {
            return null;
        }
        return max(0, $this->filter->max_liters - $this->used_liters);
    }

    public function getRemainingDays(): ?int
    {
        if (!$this->filter->max_days) {
            return null;
        }
        return max(0, $this->filter->max_days - $this->getDaysUsed());
    }

    public function getUsagePercentage(): float
    {
        $literPercentage = 100;
        $dayPercentage = 100;

        if ($this->filter->max_liters) {
            $literPercentage = ($this->used_liters / $this->filter->max_liters) * 100;
        }

        if ($this->filter->max_days) {
            $dayPercentage = ($this->getDaysUsed() / $this->filter->max_days) * 100;
        }

        return max($literPercentage, $dayPercentage);
    }

    public function needsChange(): bool
    {
        $needsChange = false;

        // Check liters limit
        if ($this->filter->max_liters && $this->used_liters >= $this->filter->max_liters) {
            $needsChange = true;
        }

        // Check days limit
        if ($this->filter->max_days && $this->getDaysUsed() >= $this->filter->max_days) {
            $needsChange = true;
        }

        return $needsChange;
    }

    public function checkAndUpdateStatus(): void
    {
        if ($this->status === self::STATUS_ACTIVE && $this->needsChange()) {
            $this->status = self::STATUS_NEED_CHANGE;
            $this->save();
        }
    }

    /**
     * Add usage to this filter and check if it needs change
     */
    public function addUsage(int $liters): void
    {
        $this->used_liters += $liters;
        $this->save();
        
        $this->checkAndUpdateStatus();
    }

    /**
     * Replace this filter
     */
    public function replace(?int $replacedBy = null, ?string $note = null): self
    {
        // Create replacement history
        FilterReplacement::create([
            'machine_filter_id' => $this->id,
            'replaced_date' => now(),
            'replaced_by' => $replacedBy,
            'old_used_liters' => $this->used_liters,
            'days_used' => $this->getDaysUsed(),
            'note' => $note,
        ]);

        // Mark current filter as changed
        $this->status = self::STATUS_CHANGED;
        $this->save();

        // Create new machine filter with same filter type
        $newMachineFilter = self::create([
            'machine_id' => $this->machine_id,
            'filter_id' => $this->filter_id,
            'install_date' => now(),
            'used_liters' => 0,
            'status' => self::STATUS_ACTIVE,
            'notes' => 'Replaced old filter',
        ]);

        return $newMachineFilter;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeNeedChange($query)
    {
        return $query->where('status', self::STATUS_NEED_CHANGE);
    }

    public function scopeByMachine($query, $machineId)
    {
        return $query->where('machine_id', $machineId);
    }
}
