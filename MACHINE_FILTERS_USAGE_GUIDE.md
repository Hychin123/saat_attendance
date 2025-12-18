# Machine Filters, Replacements & Water Usage - Complete Guide

## Overview

This system tracks **3 key aspects** of water vending machine maintenance:

1. **Machine Filters** - Active filters installed on machines
2. **Filter Replacements** - Historical record of all filter changes
3. **Water Usage** - Daily water dispensing logs

---

## 1. Machine Filters Module

### Purpose
Tracks the **7 filters** installed on each machine and their current status.

### Key Features

#### ✅ **Automatic Tracking**
- When a machine is created, **7 filters are auto-installed**
- Each filter starts with:
  - `used_liters` = 0
  - `status` = 'active'
  - `install_date` = machine install date

#### ✅ **Real-Time Status**
Filters have 3 statuses:
- 🟢 **Active** - Working normally
- 🔴 **Need Change** - Exceeded limits (auto-detected)
- ⚪ **Changed** - Replaced (historical record)

#### ✅ **Visual Indicators**
- **Days Used** - Badge showing elapsed time (green → yellow → red)
- **Liters Used** - Shows "5,000 / 15,000 L" with color coding
- **Usage %** - Overall percentage (considers both time & liters)

#### ✅ **Replace Filter Action**
Click "Replace" button on any filter:
1. Select technician
2. Add notes (reason for replacement)
3. System automatically:
   - Marks old filter as 'changed'
   - Creates replacement history record
   - Installs new filter (0L used, active status)

### How Filter Status Updates

**Automatic Check** when water usage is added:
```
If (used_liters >= max_liters) OR (days_used >= max_days):
  status = 'need_change'
```

**Example:**
- Filter: Sediment (Max: 15,000L or 180 days)
- Current: 14,800L, 170 days → Status: Active ✅
- Add 500L usage → Now: 15,300L → Status: Need Change 🔴

---

## 2. Filter Replacement Module

### Purpose
Complete **audit trail** of all filter changes across all machines.

### What It Records

Each replacement captures:
- **Machine & Filter** - Which machine, which filter type
- **Old Filter Stats**:
  - Liters used before replacement
  - Days in service
- **Replacement Info**:
  - Date replaced
  - Technician who performed replacement
  - Notes/reason
- **Timestamp** - When record was created

### Use Cases

#### 📊 **Maintenance Analytics**
- Which filters need replacement most often?
- Average lifespan per filter type
- Technician performance tracking

#### 💰 **Cost Analysis**
- How many filters replaced per month?
- Filter replacement frequency
- Budget planning

#### 🔍 **Customer Service**
- "When was last filter change?"
- "Which technician serviced my machine?"
- Complete maintenance history

### Viewing Replacements

**Filters Available:**
- Date range (from/to)
- Specific machine
- Specific technician
- Filter type

**Sorting:**
- Default: Most recent first
- Can sort by date, machine, liters, days

---

## 3. Water Usage Module

### Purpose
Track **daily water dispensing** from each machine.

### How It Works

#### Manual Entry (Current)
1. Go to "Water Usage" → Create
2. Select machine
3. Enter liters dispensed
4. Add date and notes
5. Save

#### What Happens Automatically
When water usage is saved:
```
1. Record created in machine_water_usages
2. For EACH active filter on the machine:
   - used_liters += liters_dispensed
   - Check if filter needs replacement
   - Update status if needed
```

### Features

#### ✅ **Machine Filters Updated**
All 7 filters automatically get their `used_liters` incremented.

#### ✅ **Auto Status Check**
System checks if any filter exceeds limits and updates status.

#### ✅ **Usage Reports**
- Filter by machine, date range
- Quick filters: Today, This Week, This Month
- **Total Summary** at bottom (total liters dispensed)

#### ✅ **Customer Context**
See which customer's machine is being tracked.

---

## Complete Workflow Example

### Scenario: Daily Operations

**Morning - Record Yesterday's Usage:**
```
1. Go to: Water Usage → Create
2. Machine: WVM-2025-0001
3. Date: Yesterday
4. Liters: 450L
5. Save
```

**What Happens:**
```
✅ Water usage record created
✅ All 7 filters updated:
   - Filter 1: 5,000L → 5,450L
   - Filter 2: 8,000L → 8,450L
   - Filter 3: 11,800L → 12,250L (🔴 Exceeds 12,000L limit!)
   - ... etc

✅ Filter 3 status auto-changed to 'need_change'
✅ Machine dashboard shows alert: "1 filter needs change"
```

**Maintenance - Replace Filter:**
```
1. Go to: Machine Filters
2. Filter shows: 🔴 Need Change
3. Click "Replace" button
4. Select: Technician John
5. Note: "Filter exceeded capacity"
6. Confirm
```

**What Happens:**
```
✅ Old filter marked as 'changed'
✅ Replacement history created:
   - Old used: 12,250L
   - Days used: 165 days
   - Replaced by: John
   
✅ New filter installed:
   - Same type (Carbon Block)
   - used_liters: 0L
   - status: active
   - install_date: today
```

**Result:**
```
✅ Machine back to normal operation
✅ Alert cleared
✅ Full audit trail maintained
```

---

## Navigation & Access

### Machine Management Menu

1. **Machines** - Main machine list
   - Create machines
   - Add water usage (quick action)
   - View filter status

2. **Filters** - Master filter types
   - Define filter specifications
   - Set max liters/days

3. **Machine Filters** - Currently installed filters
   - View all active filters
   - Replace filters
   - Monitor status

4. **Filter Replacements** - History
   - View all past replacements
   - Generate reports
   - Audit trail

5. **Water Usage** - Usage logs
   - Record daily usage
   - View trends
   - Generate reports

---

## Key Relationships

```
Machine (WVM-2025-0001)
├── 7 Machine Filters
│   ├── Filter 1: Sediment (active, 5,450L)
│   ├── Filter 2: Carbon Pre (active, 8,450L)
│   ├── Filter 3: Carbon Post (changed, 12,250L) ← Old
│   ├── Filter 3: Carbon Post (active, 0L) ← New
│   └── ... (4 more)
│
├── Filter Replacements
│   ├── 2025-01-15: Filter 3 replaced (12,250L, 165 days)
│   └── 2024-08-10: Filter 1 replaced (15,100L, 180 days)
│
└── Water Usage Records
    ├── 2025-12-17: 450L
    ├── 2025-12-16: 380L
    └── 2025-12-15: 520L
```

---

## Filters & Reports

### Machine Filters
**Useful Filters:**
- Status: Need Change (🔴)
- Critical (>90% used)
- By machine
- By filter type

**Use Case:** Find all filters needing immediate replacement

### Filter Replacements
**Useful Filters:**
- Date range
- By technician
- By machine

**Use Case:** Monthly maintenance report

### Water Usage
**Useful Filters:**
- Today / This Week / This Month
- By machine
- Date range

**Use Case:** 
- Daily operations tracking
- Customer billing reports
- Usage trends analysis

---

## Best Practices

### Daily Operations
1. ✅ Record water usage **daily** (or multiple times per day)
2. ✅ Check "Machine Filters" for 🔴 Need Change alerts
3. ✅ Schedule maintenance when filters show >80% usage

### Maintenance
1. ✅ Always log **who** replaced the filter (technician)
2. ✅ Add **notes** explaining why (exceeded capacity, customer complaint, etc.)
3. ✅ Verify new filter is installed (check status = active, 0L used)

### Reporting
1. ✅ Weekly: Review all machines with pending filter changes
2. ✅ Monthly: Generate replacement history report
3. ✅ Quarterly: Analyze filter lifespan and costs

---

## Advanced Features

### 1. Bulk Water Usage Entry
If you have multiple machines to update:
```php
// Via tinker or custom script
$machines = Machine::where('status', 'active')->get();

foreach ($machines as $machine) {
    $machine->addWaterUsage(500, 'Daily usage batch entry');
}
```

### 2. Filter Usage Dashboard Widget (Future)
Create widget showing:
- Total filters needing change
- Average filter lifespan
- Most frequently replaced filter type

### 3. Automated Alerts (Future)
- Email customer when filter needs change
- SMS technician for urgent replacements
- Telegram notifications

### 4. Scheduled Reports (Future)
- Weekly maintenance summary
- Monthly usage report per machine
- Quarterly cost analysis

---

## Troubleshooting

### Issue: Water usage not updating filters
**Check:**
1. Water usage saved successfully?
2. Filters are 'active' status (not 'changed')?
3. Machine has filters installed?

**Solution:**
```php
$machine = Machine::find($machineId);
$machine->addWaterUsage(500); // This should update all active filters
```

### Issue: Filter not changing to "need_change"
**Check:**
1. Filter has max_liters or max_days set?
2. Used liters exceeds max_liters?
3. Days used exceeds max_days?

**Solution:**
```php
$machineFilter = MachineFilter::find($id);
$machineFilter->checkAndUpdateStatus();
```

### Issue: Replacement not creating new filter
**Check:**
1. Old filter marked as 'changed'?
2. New filter created with same filter_id?

**Solution:**
```php
$machineFilter = MachineFilter::find($id);
$newFilter = $machineFilter->replace($technicianId, $note);
// Returns new machine filter
```

---

## API Methods Reference

### MachineFilter Model
```php
$machineFilter->getDaysUsed()              // Days since installation
$machineFilter->getRemainingLiters()       // Liters left before replacement
$machineFilter->getRemainingDays()         // Days left before replacement
$machineFilter->getUsagePercentage()       // Overall usage % (0-100)
$machineFilter->needsChange()              // Boolean: needs replacement?
$machineFilter->addUsage($liters)          // Add usage and check status
$machineFilter->replace($techId, $note)    // Replace filter, returns new one
```

### Machine Model
```php
$machine->addWaterUsage($liters, $notes)   // Record usage, update all filters
$machine->getFiltersNeedingChange()        // Count of filters needing change
$machine->hasFiltersNeedingChange()        // Boolean check
$machine->getTotalWaterDispensed()         // Total liters all-time
```

---

## Database Schema Summary

### machine_filters
```sql
id, machine_id, filter_id, install_date, 
used_liters, status, notes, 
created_at, updated_at
```

### filter_replacements
```sql
id, machine_filter_id, replaced_date, replaced_by,
old_used_liters, days_used, note,
created_at, updated_at
```

### machine_water_usages
```sql
id, machine_id, liters_dispensed, usage_date,
notes, created_at, updated_at
```

---

## Summary

✅ **Machine Filters** - Track 7 filters per machine, auto-status updates
✅ **Filter Replacements** - Complete audit trail of all changes
✅ **Water Usage** - Daily tracking that auto-updates filter usage

**Automatic Flow:**
```
Record Water Usage → Update All Active Filters → Check Limits → Auto-Update Status → Alert if Needed → Replace Filter → Create History → Install New Filter
```

**Result:** Complete, automated maintenance tracking system! 🎉
