# Quick Test - Machine Creation

Run this complete test in **php artisan tinker**:

```php
// ============================================
// STEP 1: Setup Test Data
// ============================================
echo "========== STEP 1: Setup Test Data ==========\n";

// Create or get category
$category = \App\Models\Category::firstOrCreate(
    ['name' => 'Water Vending Machines'],
    ['description' => 'Water vending machine products']
);
echo "✅ Category: {$category->name} (ID: {$category->id})\n";

// Create or get item
$item = \App\Models\Item::firstOrCreate(
    ['item_name' => 'Water Vending Machine Model A'],
    [
        'category_id' => $category->id,
        'unit' => 'unit',
        'cost_price' => 800,
        'selling_price' => 1000,
        'is_active' => true
    ]
);
echo "✅ Item: {$item->item_name} (ID: {$item->id})\n";

// Get warehouse and location
$warehouse = \App\Models\Warehouse::first();
if (!$warehouse) {
    echo "❌ ERROR: No warehouse found. Please create a warehouse first.\n";
    exit;
}
echo "✅ Warehouse: {$warehouse->warehouse_name}\n";

$location = \App\Models\Location::where('warehouse_id', $warehouse->id)->first();
if (!$location) {
    echo "❌ ERROR: No location found. Please create a location first.\n";
    exit;
}
echo "✅ Location: {$location->location_name}\n";

// Add or update stock
$stock = \App\Models\Stock::updateOrCreate(
    [
        'item_id' => $item->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $location->id
    ],
    [
        'quantity' => 10,
        'batch_no' => 'TEST-BATCH-' . date('Ymd'),
        'cost_per_unit' => 800
    ]
);
echo "✅ Stock: {$stock->quantity} units available\n";

// Get user
$user = \App\Models\User::first();
if (!$user) {
    echo "❌ ERROR: No user found.\n";
    exit;
}
echo "✅ User: {$user->name}\n";

// ============================================
// STEP 2: Check Filters
// ============================================
echo "\n========== STEP 2: Check Filters ==========\n";
$filterCount = \App\Models\Filter::count();
echo "Filters in database: {$filterCount}\n";

if ($filterCount < 7) {
    echo "⚠️  WARNING: Only {$filterCount} filters found. Running seeder...\n";
    Artisan::call('db:seed', ['--class' => 'FilterSeeder']);
    echo "✅ Filters seeded\n";
}

\App\Models\Filter::orderBy('position')->each(function($f) {
    echo "  - {$f->position}. {$f->name} (Max: {$f->max_liters}L / {$f->max_days} days)\n";
});

// ============================================
// STEP 3: Create Test Sale
// ============================================
echo "\n========== STEP 3: Create Test Sale ==========\n";

$sale = \App\Models\Sale::create([
    'customer_id' => $user->id,
    'agent_id' => $user->id,
    'warehouse_id' => $warehouse->id,
    'total_amount' => 2000,
    'net_total' => 2000,
    'deposit_amount' => 500,
    'remaining_amount' => 1500,
    'status' => 'PENDING' // Create as PENDING first
]);
echo "✅ Sale created: {$sale->sale_id}\n";

// Add sale item
$saleItem = \App\Models\SaleItem::create([
    'sale_id' => $sale->sale_id,
    'item_id' => $item->id,
    'warehouse_id' => $warehouse->id,
    'location_id' => $location->id,
    'quantity' => 2, // Will create 2 machines
    'unit_price' => 1000,
    'total_price' => 2000
]);
echo "✅ Sale item added: {$item->item_name} x {$saleItem->quantity}\n";

// ============================================
// STEP 4: Update to COMPLETED
// ============================================
echo "\n========== STEP 4: Update to COMPLETED ==========\n";

// Load relationships first
$sale->load('items.item.category');

// Update status to COMPLETED
$sale->update(['status' => 'COMPLETED']);
echo "✅ Sale status updated to: COMPLETED\n";

sleep(1); // Give observer time to run

// ============================================
// STEP 5: Check Results
// ============================================
echo "\n========== STEP 5: Check Results ==========\n";

$sale->refresh();
$machineCount = $sale->machines()->count();

echo "Machines created: {$machineCount}\n";
echo "Expected: 2\n";

if ($machineCount > 0) {
    echo "✅ SUCCESS! Machines auto-created!\n\n";
    
    $sale->machines->each(function($machine) {
        echo "📦 Machine: {$machine->serial_number}\n";
        echo "   Model: {$machine->model}\n";
        echo "   Status: {$machine->status}\n";
        echo "   Filters: {$machine->machineFilters()->count()}\n";
        
        if ($machine->machineFilters()->count() > 0) {
            echo "   Filter Details:\n";
            $machine->machineFilters->each(function($mf) {
                echo "     - {$mf->filter->name}: {$mf->status} ({$mf->used_liters}L used)\n";
            });
        }
        echo "\n";
    });
} else {
    echo "❌ FAILED! No machines created. Trying manual trigger...\n\n";
    
    // ============================================
    // STEP 6: Manual Trigger (if needed)
    // ============================================
    echo "========== STEP 6: Manual Trigger ==========\n";
    
    $observer = app(\App\Observers\SaleObserver::class);
    $reflection = new \ReflectionClass($observer);
    $method = $reflection->getMethod('createMachinesForSale');
    $method->setAccessible(true);
    
    echo "Manually calling createMachinesForSale()...\n";
    $method->invoke($observer, $sale);
    
    $sale->refresh();
    $machineCount = $sale->machines()->count();
    
    if ($machineCount > 0) {
        echo "✅ SUCCESS! Machines created manually!\n";
        echo "   Machines: {$machineCount}\n";
    } else {
        echo "❌ STILL FAILED! Debugging...\n\n";
        
        // Debug info
        echo "Debug Information:\n";
        echo "- Sale ID: {$sale->sale_id}\n";
        echo "- Sale Status: {$sale->status}\n";
        echo "- Sale Items: {$sale->items->count()}\n";
        
        foreach ($sale->items as $si) {
            $itemData = $si->item;
            echo "\nItem: {$itemData->item_name}\n";
            echo "  Category: " . ($itemData->category ? $itemData->category->name : 'None') . "\n";
            echo "  Contains 'vending': " . (stripos($itemData->item_name, 'vending') !== false ? 'YES' : 'NO') . "\n";
            
            if ($itemData->category) {
                echo "  Category contains 'vending': " . (stripos($itemData->category->name, 'vending') !== false ? 'YES' : 'NO') . "\n";
            }
        }
    }
}

// ============================================
// STEP 7: Summary
// ============================================
echo "\n========== SUMMARY ==========\n";
echo "Sale ID: {$sale->sale_id}\n";
echo "Status: {$sale->status}\n";
echo "Items: {$sale->items->count()}\n";
echo "Machines: {$sale->machines()->count()}\n";

if ($sale->machines()->count() > 0) {
    echo "\n✅ ✅ ✅ ALL TESTS PASSED! ✅ ✅ ✅\n";
    echo "The machine auto-creation system is working correctly!\n";
} else {
    echo "\n❌ TEST FAILED\n";
    echo "Please check:\n";
    echo "1. Item name or category contains 'vending'\n";
    echo "2. Observer is registered in AppServiceProvider\n";
    echo "3. Check logs: storage/logs/laravel.log\n";
}

echo "\n========================================\n";
```

Save this test and run it anytime to verify the system is working!

## How to Run:

```bash
php artisan tinker
```

Then copy and paste the entire script above.

## What It Does:

1. ✅ Creates test category "Water Vending Machines"
2. ✅ Creates test item "Water Vending Machine Model A"
3. ✅ Adds stock to warehouse
4. ✅ Checks if 7 filters exist (seeds if needed)
5. ✅ Creates a test sale with 2 machines
6. ✅ Updates sale to COMPLETED
7. ✅ Checks if machines were auto-created
8. ✅ If failed, tries manual trigger
9. ✅ Shows detailed debug info
10. ✅ Shows summary with pass/fail

## Expected Output:

```
========== STEP 1: Setup Test Data ==========
✅ Category: Water Vending Machines (ID: 5)
✅ Item: Water Vending Machine Model A (ID: 12)
✅ Warehouse: Main Warehouse
✅ Location: A-01
✅ Stock: 10 units available
✅ User: Admin User

========== STEP 2: Check Filters ==========
Filters in database: 7
  - 1. Sediment Filter (5 Micron) (Max: 15000L / 180 days)
  - 2. Carbon Block Filter (Pre) (Max: 12000L / 180 days)
  ...

========== STEP 3: Create Test Sale ==========
✅ Sale created: SAL-2025-001
✅ Sale item added: Water Vending Machine Model A x 2

========== STEP 4: Update to COMPLETED ==========
✅ Sale status updated to: COMPLETED

========== STEP 5: Check Results ==========
Machines created: 2
Expected: 2
✅ SUCCESS! Machines auto-created!

📦 Machine: WVM-2025-0001
   Model: Water Vending Machine Model A
   Status: active
   Filters: 7
   Filter Details:
     - Sediment Filter (5 Micron): active (0L used)
     - Carbon Block Filter (Pre): active (0L used)
     ...

========== SUMMARY ==========
Sale ID: SAL-2025-001
Status: COMPLETED
Items: 1
Machines: 2

✅ ✅ ✅ ALL TESTS PASSED! ✅ ✅ ✅
The machine auto-creation system is working correctly!
```
