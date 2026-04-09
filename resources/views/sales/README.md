# Sales Views Directory

This directory contains Blade templates for the Sales Management System.

## Files

### dashboard.blade.php
A beautiful dashboard view showcasing the sales management system features and workflow.

## Usage

To use these views in your routes, add to `routes/web.php`:

```php
Route::get('/sales/dashboard', function () {
    return view('sales.dashboard');
})->middleware(['auth'])->name('sales.dashboard');
```

## Adding More Views

You can add more views here for:
- Sales reports
- Commission statements
- Payment receipts
- Invoice templates
- Customer portals

## Example: Creating an Invoice View

```php
// resources/views/sales/invoice.blade.php
<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $sale->sale_id }}</title>
</head>
<body>
    <h1>Invoice</h1>
    <p>Sale ID: {{ $sale->sale_id }}</p>
    <p>Customer: {{ $sale->customer->name }}</p>
    <!-- Add more invoice details -->
</body>
</html>
```

## Related Resources

- **Filament Resources**: `app/Filament/Resources/SaleResource.php`
- **Models**: `app/Models/Sale.php`
- **Documentation**: `SALES_DOCUMENTATION.md`
