<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Categories
        $categories = [
            ['category_name' => 'Electronics', 'description' => 'Electronic devices and accessories'],
            ['category_name' => 'Food & Beverages', 'description' => 'Food items and drinks'],
            ['category_name' => 'Office Supplies', 'description' => 'Office equipment and supplies'],
            ['category_name' => 'Hardware', 'description' => 'Hardware tools and materials'],
            ['category_name' => 'Pharmaceuticals', 'description' => 'Medicine and medical supplies'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create Brands
        $brands = [
            ['brand_name' => 'Apple'],
            ['brand_name' => 'Samsung'],
            ['brand_name' => 'Nestle'],
            ['brand_name' => 'Canon'],
            ['brand_name' => 'Bosch'],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }

        // Create Suppliers
        $suppliers = [
            [
                'supplier_name' => 'Tech Distributors Inc.',
                'phone' => '+1-555-0101',
                'email' => 'tech@distributors.com',
                'address' => '123 Tech Street, Silicon Valley',
                'contact_person' => 'John Smith',
            ],
            [
                'supplier_name' => 'Food Wholesale Co.',
                'phone' => '+1-555-0202',
                'email' => 'food@wholesale.com',
                'address' => '456 Food Avenue, New York',
                'contact_person' => 'Mary Johnson',
            ],
            [
                'supplier_name' => 'Office Mart',
                'phone' => '+1-555-0303',
                'email' => 'office@mart.com',
                'address' => '789 Office Road, Chicago',
                'contact_person' => 'David Lee',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        // Create Warehouses
        $warehouses = [
            [
                'warehouse_name' => 'Main Warehouse',
                'location' => '1234 Industrial Blvd, Los Angeles, CA',
                'manager' => 'Robert Brown',
                'phone' => '+1-555-1001',
            ],
            [
                'warehouse_name' => 'North Warehouse',
                'location' => '5678 North Street, Seattle, WA',
                'manager' => 'Sarah Wilson',
                'phone' => '+1-555-1002',
            ],
            [
                'warehouse_name' => 'South Distribution Center',
                'location' => '9012 South Road, Miami, FL',
                'manager' => 'Michael Davis',
                'phone' => '+1-555-1003',
            ],
        ];

        foreach ($warehouses as $index => $warehouse) {
            $wh = Warehouse::create($warehouse);
            
            // Create locations for each warehouse
            $warehousePrefix = chr(65 + ($index * 4)); // A for first, E for second, I for third
            $racks = [$warehousePrefix, chr(ord($warehousePrefix) + 1), chr(ord($warehousePrefix) + 2), chr(ord($warehousePrefix) + 3)];
            
            foreach ($racks as $rack) {
                for ($shelf = 1; $shelf <= 5; $shelf++) {
                    for ($bin = 1; $bin <= 3; $bin++) {
                        Location::create([
                            'warehouse_id' => $wh->id,
                            'location_code' => sprintf('%s-%02d-%02d', $rack, $shelf, $bin),
                            'rack' => $rack,
                            'shelf' => (string)$shelf,
                            'bin' => (string)$bin,
                            'description' => "Rack {$rack}, Shelf {$shelf}, Bin {$bin}",
                        ]);
                    }
                }
            }
        }

        // Create Items
        $items = [
            [
                'item_name' => 'Apple iPhone 15 Pro',
                'category_id' => 1,
                'brand_id' => 1,
                'unit' => 'pcs',
                'barcode' => '123456789001',
                'cost_price' => 850.00,
                'selling_price' => 999.00,
                'has_expiry' => false,
                'reorder_level' => 10,
                'description' => 'Latest iPhone model',
            ],
            [
                'item_name' => 'Samsung Galaxy S24',
                'category_id' => 1,
                'brand_id' => 2,
                'unit' => 'pcs',
                'barcode' => '123456789002',
                'cost_price' => 750.00,
                'selling_price' => 899.00,
                'has_expiry' => false,
                'reorder_level' => 15,
                'description' => 'Samsung flagship phone',
            ],
            [
                'item_name' => 'Nestle Coffee 500g',
                'category_id' => 2,
                'brand_id' => 3,
                'unit' => 'pack',
                'barcode' => '123456789003',
                'cost_price' => 8.50,
                'selling_price' => 12.99,
                'has_expiry' => true,
                'reorder_level' => 50,
                'description' => 'Premium instant coffee',
            ],
            [
                'item_name' => 'Canon Printer Ink Cartridge',
                'category_id' => 3,
                'brand_id' => 4,
                'unit' => 'pcs',
                'barcode' => '123456789004',
                'cost_price' => 25.00,
                'selling_price' => 39.99,
                'has_expiry' => false,
                'reorder_level' => 30,
                'description' => 'Black ink cartridge',
            ],
            [
                'item_name' => 'Bosch Power Drill',
                'category_id' => 4,
                'brand_id' => 5,
                'unit' => 'pcs',
                'barcode' => '123456789005',
                'cost_price' => 85.00,
                'selling_price' => 129.99,
                'has_expiry' => false,
                'reorder_level' => 5,
                'description' => 'Cordless power drill',
            ],
            [
                'item_name' => 'Office Paper A4 (Ream)',
                'category_id' => 3,
                'brand_id' => null,
                'unit' => 'pack',
                'barcode' => '123456789006',
                'cost_price' => 3.50,
                'selling_price' => 5.99,
                'has_expiry' => false,
                'reorder_level' => 100,
                'description' => '500 sheets per ream',
            ],
            [
                'item_name' => 'Pain Relief Tablets',
                'category_id' => 5,
                'brand_id' => null,
                'unit' => 'box',
                'barcode' => '123456789007',
                'cost_price' => 4.00,
                'selling_price' => 7.99,
                'has_expiry' => true,
                'reorder_level' => 40,
                'description' => 'Pain relief medication',
            ],
            [
                'item_name' => 'Wireless Mouse',
                'category_id' => 1,
                'brand_id' => null,
                'unit' => 'pcs',
                'barcode' => '123456789008',
                'cost_price' => 12.00,
                'selling_price' => 19.99,
                'has_expiry' => false,
                'reorder_level' => 25,
                'description' => 'Wireless optical mouse',
            ],
            [
                'item_name' => 'Bottled Water 500ml (24-pack)',
                'category_id' => 2,
                'brand_id' => null,
                'unit' => 'pack',
                'barcode' => '123456789009',
                'cost_price' => 5.00,
                'selling_price' => 8.99,
                'has_expiry' => true,
                'reorder_level' => 60,
                'description' => 'Purified drinking water',
            ],
            [
                'item_name' => 'USB Flash Drive 32GB',
                'category_id' => 1,
                'brand_id' => null,
                'unit' => 'pcs',
                'barcode' => '123456789010',
                'cost_price' => 8.00,
                'selling_price' => 14.99,
                'has_expiry' => false,
                'reorder_level' => 20,
                'description' => '32GB USB 3.0',
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }

        $this->command->info('✅ Warehouse sample data created successfully!');
        $this->command->info('📦 Created:');
        $this->command->info('   - 5 Categories');
        $this->command->info('   - 5 Brands');
        $this->command->info('   - 3 Suppliers');
        $this->command->info('   - 3 Warehouses');
        $this->command->info('   - 180 Locations (60 per warehouse)');
        $this->command->info('   - 10 Sample Items');
    }
}
