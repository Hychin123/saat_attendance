# Sales Management - Quick Start Guide

## ⚡ Quick Setup (5 Minutes)

### Step 1: Run Migrations
```powershell
php artisan migrate
```

This creates 4 new tables:
- ✅ `sales`
- ✅ `sale_items`
- ✅ `payments`
- ✅ `commissions`

### Step 2: Seed Permissions
```powershell
php artisan db:seed --class=SalesPermissionsSeeder
```

This creates 19 new permissions for sales management.

### Step 3: Clear Cache
```powershell
php artisan optimize:clear
```

### Step 4: Access the System
Navigate to your Filament admin panel. You'll see a new **"Sales Management"** group with:
- 🛒 **Sales**
- 💰 **Payments**
- 💵 **Commissions**

---

## 🎯 Quick Test Workflow

### Test 1: Create a Sale

1. Go to **Sales → Create**
2. Fill in:
   - Customer: Select a user
   - Sales Agent: Select an agent (gets 5%)
   - Warehouse: Select warehouse
   - Add items with quantities and prices
3. **Save**
4. Status: `PENDING`

### Test 2: Add Deposit

1. Go to **Payments → Create**
2. Select the sale
3. Amount: $500 (example)
4. Payment Type: `DEPOSIT`
5. Payment Method: `CASH`
6. **Save**

**Result**: Sale status automatically changes to `DEPOSITED`

### Test 3: Start Processing

1. Go to **Sales → Edit** your sale
2. Change Status to `PROCESSING`
3. **Save**

**Result**: 🔥 **Stock automatically reduces!** Check `stock_movements` table.

### Test 4: Mark as Ready

1. Edit the sale
2. Change Status to `READY`
3. **Save**

### Test 5: Complete Sale

1. Go to **Payments → Create**
2. Select the same sale
3. Amount: Remaining amount
4. Payment Type: `BALANCE`
5. **Save**
6. Edit sale, change status to `COMPLETED`

**Result**: 🎉 **Commission automatically generated!** Check **Commissions** page.

---

## 📊 Business Logic Summary

| Action | Trigger | What Happens |
|--------|---------|--------------|
| Payment added | Payment created | Sale status updates |
| Status → PROCESSING | Manual change | **Stock reduces automatically** |
| Status → COMPLETED | Manual change | **Commission created (5%)** |
| Status → CANCELLED | Manual change | **Stock restored** |

---

## 🔍 Verify Everything Works

### Check 1: Stock Movement
```sql
SELECT * FROM stock_movements WHERE reference_no = 'SAL-2025-001';
```
You should see `OUT` type movements when sale is PROCESSING.

### Check 2: Commission
```sql
SELECT * FROM commissions WHERE sale_id = 'SAL-2025-001';
```
You should see commission record when sale is COMPLETED.

### Check 3: Payment Total
```sql
SELECT sale_id, SUM(amount) as total_paid FROM payments GROUP BY sale_id;
```
Should match deposit + balance payments.

---

## 🎨 Filament Features

### Sales Table Features
- ✅ Search by Sale ID, Customer, Agent
- ✅ Filter by Status, Agent, Warehouse
- ✅ Badge colors for statuses
- ✅ Money formatting
- ✅ Date range filters

### Payments Table Features
- ✅ Filter by payment type and method
- ✅ See remaining balance
- ✅ Track who received payment
- ✅ Transaction references

### Commissions Table Features
- ✅ Filter by agent
- ✅ Mark as paid with reference
- ✅ View pending commissions
- ✅ Calculate totals automatically

---

## 🚨 Common Issues

### Issue: Stock not reducing
**Solution**: Make sure:
1. SaleObserver is registered in `AppServiceProvider`
2. Status changed to exactly `PROCESSING`
3. Stock records exist for the items

### Issue: Commission not created
**Solution**: Check:
1. Sale has an `agent_id` assigned
2. Status is exactly `COMPLETED`
3. No existing commission for that sale

### Issue: Permission denied
**Solution**: Run the seeder:
```powershell
php artisan db:seed --class=SalesPermissionsSeeder
```

---

## 📈 Next Steps

1. **Customize commission rate**: Edit `SaleObserver.php` line 139
2. **Add email notifications**: Use Laravel notifications
3. **Create reports**: Use Filament widgets
4. **Add PDF export**: Use Laravel-DomPDF

---

## 💡 Pro Tips

### Tip 1: Auto-fill Agent
In `SaleResource.php`, you can auto-select logged-in user as agent:
```php
Forms\Components\Select::make('agent_id')
    ->default(auth()->id())
```

### Tip 2: Prevent Negative Stock
Add validation in `SaleItem` model:
```php
protected static function boot() {
    parent::boot();
    static::creating(function ($item) {
        if (!$item->hasStock()) {
            throw new \Exception('Insufficient stock!');
        }
    });
}
```

### Tip 3: SMS Notifications
When status is READY, send SMS to customer:
```php
if ($newStatus === Sale::STATUS_READY) {
    // Send SMS: "Your order is ready for pickup!"
}
```

---

## 📞 Support Checklist

Before asking for help:
- [ ] Migrations ran successfully
- [ ] Permissions seeded
- [ ] Observer registered in AppServiceProvider
- [ ] Cache cleared
- [ ] User has required permissions

---

**Happy Selling! 🚀**

Need help? Check `SALES_DOCUMENTATION.md` for detailed information.
