# Warehouse Management System (WMS) - Complete Documentation

## Overview
This is a complete Warehouse Management System built with Laravel and Filament, following industry best practices for inventory and warehouse operations.

## System Architecture

### 1. Database Structure

#### Master Data Tables
- **categories** - Product categories
- **brands** - Product brands
- **suppliers** - Supplier information
- **warehouses** - Warehouse locations
- **locations** - Rack/Shelf/Bin positions within warehouses
- **items** - Products/Items master data

#### Stock Tables
- **stocks** - Current stock quantities per location
- **stock_movements** - Complete audit trail of all stock changes

#### Transaction Tables
- **stock_ins** - Stock receiving records
- **stock_in_items** - Line items for stock in
- **stock_outs** - Stock dispatch records
- **stock_out_items** - Line items for stock out
- **stock_transfers** - Inter-warehouse transfers
- **stock_transfer_items** - Line items for transfers
- **stock_adjustments** - Stock corrections (damage/loss/found)

### 2. Key Features

✅ **Item Management**
- Auto-generated item codes (ITM-0001, ITM-0002, etc.)
- Category and brand classification
- Multiple units of measurement
- Barcode support
- Cost and selling price tracking
- Expiry date tracking for perishable items
- Reorder level alerts
- Image upload

✅ **Warehouse & Location**
- Multiple warehouse support
- Hierarchical location system (Rack > Shelf > Bin)
- Location codes (e.g., A-01-02)
- Same item can exist in multiple locations

✅ **Stock In (Receiving)**
- Supplier-based receiving
- Multiple items per transaction
- Batch number tracking
- Expiry date recording
- Auto-updates stock quantities
- Creates stock movement history

✅ **Stock Out (Dispatch)**
- Customer/Department tracking
- Approval workflow (Pending > Approved > Dispatched)
- Reason tracking
- Auto-deducts from stock
- Creates stock movement history

✅ **Stock Transfer**
- Warehouse-to-warehouse transfers
- Location-to-location tracking
- Approval workflow
- Status tracking (Pending > Approved > In Transit > Completed)

✅ **Stock Adjustment**
- Damage tracking
- Loss recording
- Found items
- Stock corrections
- Requires reason and approval

✅ **Stock Movement History**
- Complete audit trail
- Never deleted
- Tracks IN, OUT, TRANSFER, ADJUST operations
- User tracking
- Reference number linking

✅ **Reports & Dashboard**
- Stock overview statistics
- Low stock alerts
- Stock movement trends
- Expiring items report
- Stock by warehouse
- Fast-moving items

## Installation & Setup

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Sample Data (Optional)
Create a seeder for testing:
```bash
php artisan make:seeder WarehouseSeeder
```

### 3. Access the System
Navigate to `/admin` and log in with your admin credentials.

## Navigation Structure

### Master Data
- Categories
- Brands
- Suppliers
- Warehouses
- Locations
- Items (Products)

### Stock Operations
- Stock In (Receive Goods)
- Stock Out (Dispatch)
- Stock Transfer
- Stock Adjustment

### Reports
- Current Stock
- Stock Movements
- Low Stock Items
- Expiring Items

### Dashboard
- Overview statistics
- Stock movement chart
- Low stock alerts table

## Workflow Examples

### Receiving Goods (Stock In)
1. Navigate to "Stock In" > "Create"
2. Select supplier and warehouse
3. Add items with:
   - Item
   - Quantity
   - Location (rack/shelf/bin)
   - Batch number (if applicable)
   - Expiry date (if applicable)
   - Unit cost
4. Set status to "RECEIVED"
5. Save

**System automatically:**
- Updates stock quantities in the selected locations
- Creates stock movement record with type "IN"
- Tracks who received and when

### Dispatching Goods (Stock Out)
1. Navigate to "Stock Out" > "Create"
2. Enter customer/department name
3. Select warehouse
4. Add items with:
   - Item
   - Quantity
   - From location
   - Batch number (if specific batch)
5. Enter reason for dispatch
6. Set status to "DISPATCHED"
7. Save

**System automatically:**
- Deducts stock quantities
- Creates stock movement record with type "OUT"
- Tracks who issued and who approved

### Transferring Stock Between Warehouses
1. Navigate to "Stock Transfer" > "Create"
2. Select from warehouse and to warehouse
3. Add items with:
   - Item
   - From location
   - To location
   - Quantity
4. Set status to "COMPLETED"
5. Save

**System automatically:**
- Deducts from source location
- Adds to destination location
- Creates stock movement records with type "TRANSFER"

### Adjusting Stock (Damage/Loss)
1. Navigate to "Stock Adjustment" > "Create"
2. Select warehouse, location, and item
3. Choose adjustment type:
   - DAMAGE
   - LOSS
   - FOUND
   - CORRECTION
4. Enter quantity (negative for loss/damage)
5. Enter reason (required)
6. Request approval
7. Save

**System automatically:**
- Updates stock quantity when approved
- Creates stock movement record with type "ADJUST"

## Best Practices

### 1. Location Management
- Use consistent naming: `A-01-02` (Rack-Shelf-Bin)
- Create locations before receiving stock
- Mark inactive locations instead of deleting

### 2. Stock Movement
- Always use the proper transaction type
- Never manually edit stock quantities
- Use stock adjustment for corrections
- Include clear notes/reasons

### 3. Batch Tracking
- Use batch numbers for traceability
- Record expiry dates for perishable items
- Use FIFO (First In, First Out) for dispatch

### 4. Reports
- Check low stock daily
- Review expiring items weekly
- Audit stock movements monthly
- Reconcile physical vs system stock quarterly

## API / Integrations

### Stock Levels
Query current stock:
```php
Stock::where('item_id', $itemId)
     ->where('warehouse_id', $warehouseId)
     ->sum('quantity');
```

### Low Stock Items
```php
Item::whereColumn('reorder_level', '>', 
    DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM stocks WHERE stocks.item_id = items.id)')
)->get();
```

### Movement History
```php
StockMovement::where('item_id', $itemId)
             ->orderBy('movement_date', 'desc')
             ->get();
```

## Security & Permissions

The system uses Filament's built-in permission system. Recommended roles:

- **Warehouse Manager** - Full access
- **Stock Clerk** - Create Stock In/Out, view reports
- **Viewer** - Read-only access to reports

## Troubleshooting

### Stock Quantity Mismatch
1. Check stock_movements table for history
2. Verify all transactions have proper status
3. Run stock reconciliation report
4. Use stock adjustment to correct

### Missing Items in Stock Out
- Ensure items have sufficient quantity in the selected location
- Check the warehouse and location selection
- Verify stock exists with correct batch number

## Future Enhancements

- Barcode scanning integration
- Mobile app for stock counting
- Automated reorder suggestions
- Stock valuation reports (FIFO, LIFO, Average)
- Integration with accounting systems
- Purchase order management
- Cycle counting feature
- Multi-currency support

## Support

For issues or questions:
1. Check the stock_movements table for audit trail
2. Review Filament documentation
3. Check Laravel logs in storage/logs

## Version History

- v1.0.0 - Initial release with complete WMS features
  - Master data management
  - Stock In/Out/Transfer/Adjustment
  - Dashboard and widgets
  - Reports and filters
