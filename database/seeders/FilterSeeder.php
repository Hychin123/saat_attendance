<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Filter;

class FilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filters = [
            [
                'name' => 'Sediment Filter (5 Micron)',
                'code' => 'FILT-001',
                'description' => 'Pre-filter to remove sediment, dirt, and rust particles',
                'max_liters' => 15000,
                'max_days' => 180,
                'position' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Carbon Block Filter (Pre)',
                'code' => 'FILT-002',
                'description' => 'Removes chlorine, odors, and organic compounds',
                'max_liters' => 12000,
                'max_days' => 180,
                'position' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Carbon Block Filter (Post)',
                'code' => 'FILT-003',
                'description' => 'Further removal of taste and odor',
                'max_liters' => 12000,
                'max_days' => 180,
                'position' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'RO Membrane',
                'code' => 'FILT-004',
                'description' => 'Reverse osmosis membrane - removes dissolved solids, bacteria, viruses',
                'max_liters' => 10000,
                'max_days' => 365,
                'position' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Post Carbon Filter',
                'code' => 'FILT-005',
                'description' => 'Final polishing filter for taste improvement',
                'max_liters' => 8000,
                'max_days' => 180,
                'position' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'UV Lamp',
                'code' => 'FILT-006',
                'description' => 'Ultraviolet disinfection to kill bacteria and viruses',
                'max_liters' => 20000,
                'max_days' => 365,
                'position' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Mineralizer Filter',
                'code' => 'FILT-007',
                'description' => 'Adds beneficial minerals back to the water',
                'max_liters' => 15000,
                'max_days' => 180,
                'position' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($filters as $filter) {
            Filter::create($filter);
        }
    }
}
