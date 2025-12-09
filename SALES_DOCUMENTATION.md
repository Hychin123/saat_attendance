# Sales Management System Documentation

## Overview
Complete sales management system with commission tracking, deposit handling, and automated stock management.

---

## Business Rules

### 1. Sales Flow
```
PENDING → DEPOSITED → PROCESSING → READY → COMPLETED
```

### 2. Key Rules
- **Agent Commission**: 5% commission on net total
- **Deposit Required**: Customer must deposit money before processing
- **Processing Time**: ~1 week for order preparation
- **Stock Reduction**: Happens when status = PROCESSING
- **Commission Generation**: Happens when status = COMPLETED

---

## Database Schema

### Sales Table
```sql
CREATE TABLE sales (
    sale_id VARCHAR(50) PRIMARY KEY,
    customer_id INT,
    agent_id INT,                 -- Person who gets 5% commission
    warehouse_id INT,
    total_amount DECIMAL(12,2),
    discount DECIMAL(12,2) DEFAULT 0,
    tax DECIMAL(12,2) DEFAULT 0,
    net_total DECIMAL(12,2),
    deposit_amount DECIMAL(12,2) DEFAULT 0,
    remaining_amount DECIMAL(12,2) DEFAULT 0,
    expected_ready_date DATE,
    completed_date DATE,
    status VARCHAR(30) DEFAULT 'PENDING',
    created_at TIMESTAMP
);
```

### Sale Items Table
```sql
CREATE TABLE sale_items (
    id SERIAL PRIMARY KEY,
    sale_id VARCHAR(50),
    item_id VARCHAR(50),
    warehouse_id INT,
    location_id VARCHAR(50),
    quantity INT,
    unit_price DECIMAL(12,2),
    total_price DECIMAL(12,2)
);
```

### Payments Table
```sql
CREATE TABLE payments (
    payment_id SERIAL PRIMARY KEY,
    sale_id VARCHAR(50),
    amount DECIMAL(12,2),
    payment_type VARCHAR(20),      -- DEPOSIT / BALANCE / FULL
    payment_method VARCHAR(30),    -- CASH / BANK / QR / CREDIT_CARD
    paid_by INT,
    payment_date TIMESTAMP
);
```

### Commissions Table
```sql
CREATE TABLE commissions (
    commission_id SERIAL PRIMARY KEY,
    sale_id VARCHAR(50),
    agent_id INT,
    commission_rate DECIMAL(5,2) DEFAULT 5,
    total_sale_amount DECIMAL(12,2),
    commission_amount DECIMAL(12,2),
    status VARCHAR(20) DEFAULT 'PENDING',
    paid_date DATE,
    created_at TIMESTAMP
);
```

---

## Status Workflow

| Status | Description | Actions |
|--------|-------------|---------|
| **PENDING** | Sale created, awaiting deposit | Initial state |
| **DEPOSITED** | Customer paid deposit | Can start processing |
| **PROCESSING** | Shopping/preparation started | **Stock reduced** |
| **READY** | Items ready for pickup | Awaiting balance payment |
| **COMPLETED** | Fully paid and delivered | **Commission generated** |
| **CANCELLED** | Order cancelled | Stock restored |
| **REFUNDED** | Order returned | Stock restored |

---

## Automated Business Logic (Observer)

### SaleObserver Events

#### 1. Status → PROCESSING
```php
// Automatically reduces stock
- Updates stock quantities
- Creates StockMovement records (type: OUT)
- Reference: Sale ID
```

#### 2. Status → COMPLETED
```php
// Automatically generates commission
- Creates commission record
- Commission rate: 5%
- Commission amount = net_total × 0.05
- Status: PENDING (awaiting payment to agent)
- Sets completed_date
```

#### 3. Status → CANCELLED/REFUNDED
```php
// Automatically restores stock
- Increments stock quantities
- Creates StockMovement records (type: IN)
- Reference: Sale ID
```

---

## Example Real Case

### Scenario: $3,000 Sale with Agent John

**Step 1: Create Sale**
```
Sale ID: SAL-2025-001
Customer: Alice
Agent: John
Total: $3,000
Status: PENDING
```

**Step 2: Customer Deposits**
```
Payment: $800 (DEPOSIT)
Status: PENDING → DEPOSITED
Remaining: $2,200
```

**Step 3: Start Processing**
```
Status: DEPOSITED → PROCESSING
Action: Stock automatically reduced for all items
StockMovement created (type: OUT)
```

**Step 4: Items Ready**
```
Status: PROCESSING → READY
Expected ready date: ~1 week
```

**Step 5: Customer Pays Balance**
```
Payment: $2,200 (BALANCE)
Status: READY → COMPLETED
Remaining: $0
```

**Step 6: Auto-Commission**
```
Commission created automatically:
- Agent: John
- Sale Amount: $3,000
- Rate: 5%
- Commission: $150
- Status: PENDING
```

---

## Filament Resources

### 1. Sales Resource
**Location**: `app/Filament/Resources/SaleResource.php`

**Features**:
- Create/Edit sales with items
- Auto-calculate totals
- Track deposits and remaining amounts
- Status management
- Agent assignment

**Form Fields**:
- Sale ID (auto-generated: SAL-YYYY-###)
- Customer, Agent, Warehouse
- Sale items (repeater)
- Discount, Tax, Totals
- Deposit amount
- Expected ready date
- Status

### 2. Payments Resource
**Location**: `app/Filament/Resources/PaymentResource.php`

**Features**:
- Record deposits and balance payments
- Multiple payment methods
- Auto-update sale status
- Track who received payment

**Form Fields**:
- Sale selection
- Amount
- Payment type (DEPOSIT/BALANCE/FULL)
- Payment method (CASH/BANK/QR/CREDIT_CARD)
- Transaction reference
- Payment date

### 3. Commissions Resource
**Location**: `app/Filament/Resources/CommissionResource.php`

**Features**:
- View all commissions
- Filter by agent
- Mark as paid
- Track payment status
- Auto-calculate 5% commission

**Table Actions**:
- Mark as Paid (with payment reference)
- View/Edit commission details
- Filter by status, agent, date

---

## Models & Relationships

### Sale Model
```php
// Relationships
customer() -> BelongsTo User
agent() -> BelongsTo User
warehouse() -> BelongsTo Warehouse
items() -> HasMany SaleItem
payments() -> HasMany Payment
commissions() -> HasMany Commission

// Helper Methods
calculateTotals()
isFullyPaid()
canProcess()
canComplete()

// Scopes
pending()
deposited()
processing()
ready()
completed()
byAgent($agentId)
byCustomer($customerId)
```

### Payment Model
```php
// Relationships
sale() -> BelongsTo Sale
paidBy() -> BelongsTo User

// Scopes
deposits()
balances()
bySale($saleId)
byMethod($method)
```

### Commission Model
```php
// Relationships
sale() -> BelongsTo Sale
agent() -> BelongsTo User

// Helper Methods
calculateCommission()
markAsPaid($reference)

// Scopes
pending()
paid()
byAgent($agentId)
forPeriod($start, $end)
```

---

## Reports Available

### 1. Agent Commission Report
- Total commissions per agent
- Pending vs Paid commissions
- Commission trends

### 2. Deposit vs Remaining
- Sales with outstanding balances
- Deposit collection rate
- Payment tracking

### 3. Sales by Agent
- Agent performance
- Total sales per agent
- Conversion rates

### 4. Stock by Status
- Stock reserved (PROCESSING)
- Available stock
- Stock movements

### 5. Weekly Financial Report
- Weekly sales total
- Payments collected
- Commissions owed

### 6. Top Performing Agents
- Highest sales volume
- Most commissions earned
- Best conversion rates

---

## Installation Steps

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Permissions
```bash
php artisan db:seed --class=SalesPermissionsSeeder
```

### 3. Verify Observer Registration
Check `app/Providers/AppServiceProvider.php`:
```php
Sale::observe(SaleObserver::class);
```

### 4. Test the Flow
1. Create a sale (Status: PENDING)
2. Add a payment (Status: DEPOSITED)
3. Change to PROCESSING (Stock reduces)
4. Change to READY
5. Pay balance and set COMPLETED (Commission generates)

---

## API Endpoints (Optional)

If you need API access, create routes in `routes/api.php`:

```php
Route::prefix('sales')->group(function () {
    Route::get('/', [SaleController::class, 'index']);
    Route::post('/', [SaleController::class, 'store']);
    Route::get('/{id}', [SaleController::class, 'show']);
    Route::put('/{id}', [SaleController::class, 'update']);
    
    // Status updates
    Route::post('/{id}/deposit', [SaleController::class, 'addDeposit']);
    Route::post('/{id}/process', [SaleController::class, 'startProcessing']);
    Route::post('/{id}/complete', [SaleController::class, 'complete']);
});

Route::prefix('commissions')->group(function () {
    Route::get('/agent/{agentId}', [CommissionController::class, 'byAgent']);
    Route::post('/{id}/pay', [CommissionController::class, 'markAsPaid']);
});
```

---

## Security & Permissions

### Required Permissions
- `view_any_sale`, `view_sale`, `create_sale`, `update_sale`, `delete_sale`
- `view_any_payment`, `view_payment`, `create_payment`, `update_payment`
- `view_any_commission`, `view_commission`, `create_commission`, `update_commission`

### Policy Files
- `app/Policies/SalePolicy.php`
- `app/Policies/PaymentPolicy.php`
- `app/Policies/CommissionPolicy.php`

---

## Troubleshooting

### Stock Not Reducing
- Check if status changed to PROCESSING
- Verify SaleObserver is registered
- Check stock records exist for items

### Commission Not Generated
- Verify sale status is COMPLETED
- Check if agent_id is assigned
- Ensure SaleObserver is working

### Payment Not Updating Sale
- Check PaymentResource CreatePayment page
- Verify afterCreate() hook is executed
- Check sale relationships

---

## Future Enhancements

1. **Multi-currency support**
2. **Partial refunds**
3. **Commission tiers** (different rates for different agents)
4. **Sales quotas and targets**
5. **Customer loyalty points**
6. **Email notifications** for status changes
7. **PDF invoices and receipts**
8. **SMS notifications** for ready orders
9. **Dashboard widgets** for sales metrics
10. **Inventory reservation** before processing

---

## Support

For issues or questions:
1. Check observer logs
2. Verify migrations ran successfully
3. Ensure permissions are seeded
4. Check Filament resources are registered

---

**Created**: December 8, 2025  
**System Version**: 1.0  
**Laravel Version**: 10.x  
**Filament Version**: 3.x
