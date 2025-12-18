<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'serial_number',
        'model',
        'sale_id',
        'customer_id',
        'install_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'install_date' => 'date',
    ];

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_MAINTENANCE = 'maintenance';
    public const STATUS_DECOMMISSIONED = 'decommissioned';

    public static function getStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_MAINTENANCE => 'Maintenance',
            self::STATUS_DECOMMISSIONED => 'Decommissioned',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($machine) {
            if (empty($machine->serial_number)) {
                $machine->serial_number = self::generateSerialNumber();
            }
        });

        static::created(function ($machine) {
            // Auto-create 7 filters when machine is created
            $machine->initializeFilters();
        });
    }

    public static function generateSerialNumber(): string
    {
        $year = date('Y');
        $lastMachine = self::withTrashed()
            ->where('serial_number', 'LIKE', "WVM-{$year}-%")
            ->orderBy('serial_number', 'desc')
            ->first();

        if ($lastMachine) {
            $lastNumber = (int) substr($lastMachine->serial_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "WVM-{$year}-{$newNumber}";
    }

    /**
     * Initialize 7 filters for the machine
     */
    public function initializeFilters(): void
    {
        $filters = Filter::where('is_active', true)
            ->orderBy('position')
            ->limit(7)
            ->get();

        foreach ($filters as $filter) {
            MachineFilter::create([
                'machine_id' => $this->id,
                'filter_id' => $filter->id,
                'install_date' => $this->install_date ?? now(),
                'used_liters' => 0,
                'status' => 'active',
            ]);
        }
    }

    /**
     * Relationships
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id', 'sale_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function machineFilters(): HasMany
    {
        return $this->hasMany(MachineFilter::class);
    }

    public function activeFilters(): HasMany
    {
        return $this->hasMany(MachineFilter::class)->where('status', 'active');
    }

    public function waterUsages(): HasMany
    {
        return $this->hasMany(MachineWaterUsage::class);
    }

    /**
     * Helper methods
     */
    public function getTotalWaterDispensed(): int
    {
        return $this->waterUsages()->sum('liters_dispensed');
    }

    public function getFiltersNeedingChange(): int
    {
        return $this->machineFilters()->where('status', 'need_change')->count();
    }

    public function hasFiltersNeedingChange(): bool
    {
        return $this->getFiltersNeedingChange() > 0;
    }

    /**
     * Add water usage and update all active filters
     */
    public function addWaterUsage(int $liters, ?string $notes = null): void
    {
        // Record the usage
        MachineWaterUsage::create([
            'machine_id' => $this->id,
            'liters_dispensed' => $liters,
            'usage_date' => now(),
            'notes' => $notes,
        ]);

        // Update all active filters
        $activeFilters = $this->activeFilters;
        foreach ($activeFilters as $machineFilter) {
            $machineFilter->addUsage($liters);
        }
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->whereHas('machineFilters', function ($q) {
            $q->where('status', 'need_change');
        });
    }
}
