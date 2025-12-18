# Water Vending Machine Filter Tracking System

## Overview

This system tracks water vending machines, their installed filters, filter usage, and maintenance schedules.

## System Architecture

### 1. Database Structure

#### **machines** table
- `id` - Primary key
- `serial_number` - Auto-generated (WVM-YYYY-####)
- `model` - Machine model name
- `sale_id` - Foreign key to sales table
- `customer_id` - Foreign key to users table
- `install_date` - Installation date
- `status` - active, inactive, maintenance, decommissioned
- `notes` - Additional notes

#### **filters** table
- `id` - Primary key
- `name` - Filter name (e.g., "Sediment Filter")
- `code` - Auto-generated (FILT-###)
- `description` - Filter description
- `max_liters` - Maximum liters before replacement
- `max_days` - Maximum days before replacement
- `position` - Position in 7-filter system (1-7)
- `is_active` - Active status

**Default 7 Filters:**
1. Sediment Filter (5 Micron) - 15,000L / 180 days
2. Carbon Block Filter (Pre) - 12,000L / 180 days
3. Carbon Block Filter (Post) - 12,000L / 180 days
4. RO Membrane - 10,000L / 365 days
5. Post Carbon Filter - 8,000L / 180 days
6. UV Lamp - 20,000L / 365 days
7. Mineralizer Filter - 15,000L / 180 days

#### **machine_filters** table
- `id` - Primary key
- `machine_id` - Foreign key to machines
- `filter_id` - Foreign key to filters
- `install_date` - Filter installation date
- `used_liters` - Total liters processed
- `status` - active, need_change, changed
- `notes` - Additional notes

#### **filter_replacements** table
- `id` - Primary key
- `machine_filter_id` - Foreign key to machine_filters
- `replaced_date` - Replacement date
- `replaced_by` - Technician user ID
- `old_used_liters` - Liters used when replaced
- `days_used` - Days since installation
- `note` - Replacement notes

#### **machine_water_usages** table
- `id` - Primary key
- `machine_id` - Foreign key to machines
- `liters_dispensed` - Liters dispensed
- `usage_date` - Date of usage
- `notes` - Additional notes

---

## Complete System Flow

### Step 1: Sale Creation

```
1. Create sale in Sales Module
   └─ Add items (including water vending machines)
   └─ Sale status: PENDING → DEPOSITED → PROCESSING → READY → COMPLETED
```

**When Sale Status = COMPLETED:**
- ✅ Stock is deducted (already handled by existing system)
- ✅ Commission is generated (already handled by existing system)
- ✅ **NEW:** Machines are auto-created

### Step 2: Automatic Machine Creation

**Trigger:** Sale status changed to COMPLETED

**Process (in `SaleObserver`):**
```php
1. Check each sale item
2. If item is a "Water Vending Machine" (checked by category or name):
   a. Create Machine record(s) based on quantity
   b. Auto-generate serial number (WVM-2025-0001)
   c. Link to sale and customer
   d. Set status to 'active'
```

**Identification Logic:**
```php
// Item is considered a water vending machine if:
- Category name contains "vending" OR
- Item name contains "vending" or "water machine"
```

### Step 3: Auto-Initialize 7 Filters

**Trigger:** Machine created

**Process (in `Machine` model boot):**
```php
When machine is created:
1. Get 7 active filters (ordered by position)
2. For each filter:
   - Create machine_filter record
   - install_date = machine install_date
   - used_liters = 0
   - status = 'active'
```

**Result:** Each new machine automatically has 7 active filters ready to track.

---

## Daily Operations

### Adding Water Usage

**Manual Process:**
```
1. Go to Machines list
2. Click "Add Water Usage" button on machine row
3. Enter liters dispensed
4. System automatically:
   a. Creates water_usage record
   b. Adds liters to ALL active filters
   c. Checks if any filter exceeds limits
   d. Updates filter status to 'need_change' if exceeded
```

**Backend Logic:**
```php
$machine->addWaterUsage(500); // 500 liters dispensed

For each active filter:
  used_liters += 500
  
  // Check limits
  if (used_liters >= max_liters OR days_used >= max_days):
    status = 'need_change'
```

### Filter Status Checking

**Automatic Check** (when usage is added):
```php
Filter needs change IF:
- used_liters >= filter.max_liters OR
- days_used >= filter.max_days

Status automatically changes to 'need_change'
```

**Visual Indicators:**
- 🟢 **Active** - Filter is working fine
- 🔴 **Need Change** - Filter exceeded limit
- ⚪ **Changed** - Filter has been replaced (historical)

---

## Filter Replacement Process

### 1. Identify Filters Needing Change

**Dashboard View:**
```
Machine WVM-2025-0001
├─ Filter 1: Active (5,000L / 15,000L) ✅
├─ Filter 2: Need Change (12,500L / 12,000L) 🔴
├─ Filter 3: Active (8,000L / 12,000L) ✅
└─ Filter 4: Need Change (190 days / 180 days) 🔴
```

### 2. Replace Filter

**Process:**
```
1. Technician goes to Machine > Filters tab
2. Click "Replace Filter" on filter needing change
3. System automatically:
   a. Create replacement history record
      - Old used_liters
      - Days used
      - Technician ID
      - Replacement date
   
   b. Mark old filter as 'changed'
   
   c. Create NEW machine_filter record
      - Same filter_id (same type)
      - install_date = TODAY
      - used_liters = 0
      - status = 'active'
```

**Backend:**
```php
$machineFilter->replace($technicianId, $note);

// Creates:
filter_replacements (history)
machine_filters (new active filter)
```

---

## Reports & Monitoring

### Machine Dashboard
- Total machines active
- Machines needing maintenance (with filters needing change)
- Total water dispensed
- Filter replacement history

### Filter Usage Analytics
- Which filters need replacement most frequently
- Average lifespan per filter type
- Cost analysis (if prices added)

---

## Implementation Summary

### ✅ Created Models:
1. `Machine` - Tracks each vending machine
2. `Filter` - Master list of filter types
3. `MachineFilter` - Filters installed on machines
4. `FilterReplacement` - Replacement history
5. `MachineWaterUsage` - Daily water usage tracking

### ✅ Created Migrations:
- All 5 tables with proper relationships

### ✅ Updated Existing:
- `Sale` model - Added `machines()` relationship
- `SaleObserver` - Auto-creates machines on sale completion

### ✅ Seeded Data:
- 7 default filter types

### ✅ Filament Resources:
- Machine Management (with add usage action)
- Filter Master Data
- Machine Filter tracking
- Filter Replacement history
- Water Usage logs

---

## Usage Example

### Scenario: New Machine Sale

```
1. Customer orders 1 Water Vending Machine
   └─ Create Sale: SAL-2025-001
   └─ Add item: "Water Vending Machine Model A" (qty: 1)
   └─ Status: COMPLETED

2. System automatically:
   ✅ Creates Machine: WVM-2025-0001
   ✅ Creates 7 machine_filters (all active)
   
3. Daily operations:
   Day 1: Add 500L usage
   Day 2: Add 450L usage
   Day 3: Add 600L usage
   ...
   Day 120: Add 500L
   
   Filter 2 reaches 12,000L limit
   └─ Status automatically changes to 'need_change'
   └─ Dashboard shows alert: "1 filter needs change"

4. Maintenance:
   Technician replaces Filter 2
   ✅ Old filter marked as 'changed'
   ✅ New Filter 2 created (0L used)
   ✅ Replacement recorded in history
   ✅ Alert cleared
```

---

## API Methods

### Machine Model
```php
$machine->addWaterUsage($liters, $notes)
$machine->getFiltersNeedingChange()
$machine->hasFiltersNeedingChange()
$machine->getTotalWaterDispensed()
```

### MachineFilter Model
```php
$machineFilter->needsChange()
$machineFilter->checkAndUpdateStatus()
$machineFilter->addUsage($liters)
$machineFilter->replace($technicianId, $note)
$machineFilter->getDaysUsed()
$machineFilter->getRemainingLiters()
$machineFilter->getRemainingDays()
$machineFilter->getUsagePercentage()
```

---

## Future Enhancements

1. **Automated Alerts**
   - Email/SMS when filter needs change
   - Telegram notifications

2. **Predictive Maintenance**
   - Predict when filters will need change
   - Schedule maintenance in advance

3. **Mobile App**
   - Technicians can log water usage from mobile
   - Scan QR code on machine

4. **Cost Tracking**
   - Track filter replacement costs
   - Customer billing for filter changes

5. **IoT Integration**
   - Automatic water usage recording
   - Real-time monitoring

---

## Configuration

### Identifying Water Vending Machines

Edit `app/Observers/SaleObserver.php`:

```php
protected function isWaterVendingMachine(Item $item): bool
{
    // Option 1: By category
    if ($item->category && stripos($item->category->name, 'vending') !== false) {
        return true;
    }

    // Option 2: By item name
    if (stripos($item->item_name, 'vending') !== false) {
        return true;
    }

    // Option 3: Add custom field to items table
    // if ($item->is_machine) {
    //     return true;
    // }

    return false;
}
```

---

## Troubleshooting

### Machines not auto-created?
- Check if sale status is COMPLETED
- Check if item matches identification logic
- Check SaleObserver is registered

### Filters not initialized?
- Check FilterSeeder was run
- Ensure 7 filters exist in database
- Check Machine boot() method

### Filter status not updating?
- Ensure water usage is added via `addWaterUsage()` method
- Check filter max_liters and max_days are set
- Verify `checkAndUpdateStatus()` is called

---

## Database Seeds

To reseed filters:
```bash
php artisan db:seed --class=FilterSeeder
```

To reset and reseed:
```bash
php artisan migrate:fresh --seed
```
