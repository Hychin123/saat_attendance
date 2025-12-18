<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_liters',
        'max_days',
        'position',
        'is_active',
    ];

    protected $casts = [
        'max_liters' => 'integer',
        'max_days' => 'integer',
        'position' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($filter) {
            if (empty($filter->code)) {
                $filter->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $lastFilter = self::orderBy('id', 'desc')->first();
        
        if ($lastFilter) {
            $lastNumber = (int) substr($lastFilter->code, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "FILT-{$newNumber}";
    }

    /**
     * Relationships
     */
    public function machineFilters(): HasMany
    {
        return $this->hasMany(MachineFilter::class);
    }

    /**
     * Helper methods
     */
    public function getActiveInstallationsCount(): int
    {
        return $this->machineFilters()->where('status', 'active')->count();
    }

    public function getTotalInstallationsCount(): int
    {
        return $this->machineFilters()->count();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPosition($query, $position)
    {
        return $query->where('position', $position);
    }
}
