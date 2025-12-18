# Testing Machine Auto-Creation

## Quick Test Guide

### Step 1: Verify Database Setup

Check that all tables exist:
```bash
php artisan migrate:status
```

Check that filters are seeded:
```bash
php artisan tinker
>>> \App\Models\Filter::count()
# Should return 7
```

### Step 2: Create Test Category and Item

**Option A: Via Filament UI**
1. Go to Categories → Create new category named "Water Vending Machines"
2. Go to Items → Create new item:
   - Name: "Water Vending Machine Model A"
   - Category: "Water Vending Machines"
   - Price: $1000

**Option B: Via Tinker**
```bash
php artisan tinker
```

```php
// Create category
$category = \App\Models\Category::create([
    'name' => 'Water Vending Machines',
    'description' => 'Water vending machine products'
]);

// Create item
$item = \App\Models\Item::create([
    'item_name' => 'Water Vending Machine Model A',
    'category_id' => $category->id,
    'unit' => 'unit',
    'cost_price' => 800,
    'selling_price' => 1000,
    'is_active' => true
]);

// Add stock to a warehouse
$warehouse = \App\Models\Warehouse::first();
$location = \App\Models\Location::where('warehouse_id', $warehouse->id)->first();

\App\Models\Stock::create([
    'item_id' => $item->id,
    'warehouse_id' => $warehouse->id,
    'location_id' => $location->id,
    'quantity' => 10,
    'batch_no' => 'BATCH-001',
    'cost_per_unit' => 800,
    'expiry_date' => null
]);
```

### Step 3: Create a Test Sale

**Via Filament UI:**
1. Go to Sales → Create Sale
2. Select customer, agent, warehouse
3. Add item: "Water Vending Machine Model A" (qty: 1)
4. Set status to "COMPLETED"
5. Save

**Via Tinker:**
```php
$sale = \App\Models\Sale::create([
    'customer_id' => 1, // Adjust to valid user ID
    'agent_id' => 1,    // Adjust to valid user ID
    'warehouse_id' => 1, // Adjust to valid warehouse ID
    'total_amount' => 1000,
    'net_total' => 1000,
    'status' => 'PENDING' // Create as PENDING first
]);

$item = \App\Models\Item::where('item_name', 'LIKE', '%vending%')->first();

\App\Models\SaleItem::create([
    'sale_id' => $sale->sale_id,
    'item_id' => $item->id,
    'warehouse_id' => 1,
    'quantity' => 1,
    'unit_price' => 1000,
    'total_price' => 1000
]);

// Now update to COMPLETED to trigger machine creation
$sale->update(['status' => 'COMPLETED']);
```

### Step 4: Verify Machine Creation

**Check in database:**
```php
php artisan tinker
>>> $sale = \App\Models\Sale::latest()->first()
>>> $sale->machines
# Should show created machines

>>> $sale->machines()->count()
# Should return 1 (or more based on quantity)

>>> $machine = $sale->machines->first()
>>> $machine->machineFilters()->count()
# Should return 7 (7 filters auto-created)
```

**Check in Filament UI:**
1. Go to Machine Management → Machines
2. You should see the newly created machine with serial number WVM-YYYY-####

---

## Debugging Guide

### Issue: Machines Not Created

**Debug Step 1: Check if item is recognized as vending machine**

```php
php artisan tinker
```

```php
$item = \App\Models\Item::where('item_name', 'LIKE', '%vending%')->first();

// Test the identification logic
if ($item->category && stripos($item->category->name, 'vending') !== false) {
    echo "✅ Detected by category\n";
}

if (stripos($item->item_name, 'vending') !== false) {
    echo "✅ Detected by item name\n";
}

if (stripos($item->item_name, 'water machine') !== false) {
    echo "✅ Detected by water machine keyword\n";
}
```

**Debug Step 2: Manually trigger machine creation**

```php
$sale = \App\Models\Sale::where('status', 'COMPLETED')->latest()->first();
$sale->load('items.item.category');

$observer = app(\App\Observers\SaleObserver::class);
$reflection = new \ReflectionClass($observer);
$method = $reflection->getMethod('createMachinesForSale');
$method->setAccessible(true);
$method->invoke($observer, $sale);

// Check result
$sale->machines()->count();
```

**Debug Step 3: Use the command**

```bash
# Process all completed sales
php artisan machines:create-from-sales

# Process specific sale
php artisan machines:create-from-sales --sale_id=SAL-2025-001
```

**Debug Step 4: Check observer is working**

Add temporary logging to SaleObserver:

```php
// In app/Observers/SaleObserver.php
protected function createMachinesForSale(Sale $sale): void
{
    \Log::info('createMachinesForSale called', ['sale_id' => $sale->sale_id]);
    
    if ($sale->machines()->count() > 0) {
        \Log::info('Machines already exist, skipping');
        return;
    }
    
    $saleItems = $sale->items;
    \Log::info('Found sale items', ['count' => $saleItems->count()]);
    
    foreach ($saleItems as $saleItem) {
        $item = $saleItem->item;
        \Log::info('Checking item', [
            'item_id' => $item->id,
            'item_name' => $item->item_name,
            'is_vending' => $this->isWaterVendingMachine($item)
        ]);
        
        if ($this->isWaterVendingMachine($item)) {
            \Log::info('Creating machines', ['quantity' => $saleItem->quantity]);
            // ... rest of the code
        }
    }
}
```

Then check logs:
```bash
tail -f storage/logs/laravel.log
```

---

## Manual Creation via Filament Action Button

If auto-creation doesn't work, you can manually create machines:

1. Go to Sales list
2. Find a COMPLETED sale
3. If it has no machines, you'll see a "Create Machines" button
4. Click it to manually trigger machine creation
5. A notification will show how many machines were created

---

## Common Issues & Solutions

### Issue 1: Observer not firing
**Solution:** Check AppServiceProvider.php that observer is registered:
```php
use App\Observers\SaleObserver;
use App\Models\Sale;

// In boot() method:
Sale::observe(SaleObserver::class);
```

### Issue 2: Item not detected as vending machine
**Solution:** Ensure item name or category contains "vending" or "water machine"
- Category name: "Water Vending Machines"
- OR Item name: "Water Vending Machine Model A"

### Issue 3: Filters not initialized
**Solution:** Run filter seeder:
```bash
php artisan db:seed --class=FilterSeeder
```

### Issue 4: Sale created but observer skips creation
**Solution:** Sale status must be COMPLETED. Check:
```php
$sale->status === 'COMPLETED' // Must be true
```

### Issue 5: Items not loaded
**Solution:** Ensure items are created before calling observer:
```php
$sale->load('items.item.category');
```

---

## Complete Test Script

Run this in `php artisan tinker` for a complete test:

```php
// 1. Create category
$category = \App\Models\Category::firstOrCreate(
    ['name' => 'Water Vending Machines'],
    ['description' => 'Water vending machine products']
);

// 2. Create item
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

// 3. Add stock
$warehouse = \App\Models\Warehouse::first();
$location = \App\Models\Location::where('warehouse_id', $warehouse->id)->first();

$stock = \App\Models\Stock::firstOrCreate(
    [
        'item_id' => $item->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $location->id
    ],
    [
        'quantity' => 10,
        'batch_no' => 'BATCH-TEST-001',
        'cost_per_unit' => 800
    ]
);

// 4. Create sale
$user = \App\Models\User::first();

$sale = \App\Models\Sale::create([
    'customer_id' => $user->id,
    'agent_id' => $user->id,
    'warehouse_id' => $warehouse->id,
    'total_amount' => 1000,
    'net_total' => 1000,
    'deposit_amount' => 0,
    'remaining_amount' => 1000,
    'status' => 'PENDING'
]);

// 5. Add sale item
\App\Models\SaleItem::create([
    'sale_id' => $sale->sale_id,
    'item_id' => $item->id,
    'warehouse_id' => $warehouse->id,
    'location_id' => $location->id,
    'quantity' => 2, // Create 2 machines
    'unit_price' => 1000,
    'total_price' => 2000
]);

// 6. Update to COMPLETED
$sale->load('items.item.category');
$sale->update(['status' => 'COMPLETED']);

// 7. Check results
echo "Machines created: " . $sale->machines()->count() . "\n";
echo "Expected: 2\n";

$sale->machines->each(function($machine) {
    echo "- Machine: {$machine->serial_number}\n";
    echo "  Filters: {$machine->machineFilters()->count()}\n";
});

// If no machines created, manually trigger
if ($sale->machines()->count() === 0) {
    echo "Manually triggering creation...\n";
    $observer = app(\App\Observers\SaleObserver::class);
    $reflection = new \ReflectionClass($observer);
    $method = $reflection->getMethod('createMachinesForSale');
    $method->setAccessible(true);
    $method->invoke($observer, $sale);
    
    echo "Machines now: " . $sale->machines()->count() . "\n";
}
```

---

## Success Indicators

✅ **Everything working correctly:**
1. Sale status = COMPLETED
2. Sale has machines (count > 0)
3. Each machine has 7 filters
4. Each filter has status = 'active'
5. Each filter has used_liters = 0

**Verify:**
```php
$sale = \App\Models\Sale::with('machines.machineFilters')->latest()->first();
echo "Sale: {$sale->sale_id}\n";
echo "Status: {$sale->status}\n";
echo "Machines: {$sale->machines->count()}\n";

$sale->machines->each(function($m) {
    echo "- {$m->serial_number}: {$m->machineFilters->count()} filters\n";
});
```
