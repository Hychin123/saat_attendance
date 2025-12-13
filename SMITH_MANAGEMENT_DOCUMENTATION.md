# Smith Management System Documentation

## Overview
The Smith Management System is a comprehensive module for tracking material usage, adjustments, returns, and stock issues for users with the "Smith" role.

## Features

### 1. Material Used
Track materials used by smiths for their projects.
- **Reference Number**: Auto-generated (MU-YYYY-###)
- **Fields**: Smith, Warehouse, Item, Quantity, Usage Date, Project Name, Purpose, Notes
- **Status**: Pending, Approved, Rejected
- **Access**: Smiths can create and view their own records; HR/Warehouse Managers can approve

### 2. Material Adjustments
Adjust material quantities with full audit trail.
- **Reference Number**: Auto-generated (MA-YYYY-###)
- **Fields**: Smith, Warehouse, Item, Adjustment Type (Add/Subtract), Quantity, Previous/New Quantity, Reason
- **Status**: Pending, Approved, Rejected
- **Access**: Requires approval from HR/Warehouse Manager

### 3. Smith Returns
Return defective or damaged materials with optional replacement.
- **Reference Number**: Auto-generated (SR-YYYY-###)
- **Fields**: Defective Item, Quantity, Return Reason, Replacement Item, Description
- **Return Reasons**: Defective, Damaged, Wrong Item, Quality Issue, Other
- **Status**: Pending, Approved, Rejected, Completed
- **Access**: Tracks both approval and processing

### 4. Smith Stock Issues
Request materials from stock for projects.
- **Reference Number**: Auto-generated (SSI-YYYY-###)
- **Fields**: Smith, Warehouse, Item, Quantity, Issue Date, Project Name, Purpose
- **Status**: Pending, Issued, Rejected
- **Access**: Smiths request; Warehouse staff issues

## Database Schema

### Tables Created
1. **material_used** - Tracks material usage by smiths
2. **material_adjustments** - Material quantity adjustments
3. **smith_returns** - Defective material returns
4. **smith_stock_issues** - Material requests from stock

### Key Relationships
- All tables link to `users` (smith user)
- All tables link to `warehouses`
- All tables link to `items` (materials)
- Support for approvers and processors

## Models

### Files Created
- `app/Models/MaterialUsed.php`
- `app/Models/MaterialAdjustment.php`
- `app/Models/SmithReturn.php`
- `app/Models/SmithStockIssue.php`

### Key Features
- Auto-generating reference numbers
- Relationship definitions
- Proper casting for dates and decimals
- Status tracking

## Filament Resources

### Resource Files
Each entity has a complete Filament resource with:
- List page with filters and search
- Create/Edit forms with validation
- View page for details
- Badge columns for status visualization

### Navigation
All resources are grouped under "Smith Management" in the navigation menu.

### Access Control
Resources respect policy permissions:
- Smiths see only their own records (except when viewing all)
- HR/Warehouse Managers can see and approve all records
- Super Admin has full access

## Policies

### Files Created
- `app/Policies/MaterialUsedPolicy.php`
- `app/Policies/MaterialAdjustmentPolicy.php`
- `app/Policies/SmithReturnPolicy.php`
- `app/Policies/SmithStockIssuePolicy.php`

### Access Rules
1. **View Any**: Smith, HR Manager, Warehouse Manager, Super Admin
2. **View**: Smiths can view their own; Managers can view all
3. **Create**: All authorized roles can create
4. **Update**: Smiths can update only pending records; Managers can update all
5. **Delete**: Only Managers and Super Admin
6. **Approve**: Only HR/Warehouse Managers and Super Admin

## Roles

### New Roles Added
1. **Smith**
   - Department: Production
   - Description: Handles material usage, adjustments, returns, and stock issues

2. **Warehouse Manager**
   - Department: Operations
   - Description: Manages warehouse operations and approves smith requests

### Sample Users
- Smith User: smith.user@example.com (password: password)
- Warehouse Manager: warehouse.manager@example.com (password: password)

## Installation Steps

### 1. Migrations
```bash
php artisan migrate
```
This creates the four new tables for smith management.

### 2. Seed Data
```bash
php artisan db:seed --class=SmithRoleSeeder
```
This creates:
- Smith role
- Warehouse Manager role
- Sample smith user
- Sample warehouse manager user

### 3. Access the System
1. Login with a smith user account
2. Navigate to "Smith Management" in the sidebar
3. Start creating material records

## Workflow Examples

### Material Usage Workflow
1. Smith creates a material usage record
2. Record is in "Pending" status
3. HR/Warehouse Manager reviews and approves/rejects
4. Smith can view approval status

### Return and Replacement Workflow
1. Smith identifies defective material
2. Creates a return record with description
3. Optionally specifies replacement item
4. Manager approves the return
5. Warehouse staff processes the return (status: Completed)
6. Replacement item is issued if specified

### Stock Issue Workflow
1. Smith requests material from stock
2. Specifies project and purpose
3. Warehouse staff reviews request
4. If approved, material is issued
5. Record updated to "Issued" status

## Future Enhancements

Potential improvements:
1. Integration with stock levels (auto-deduct on approval)
2. Email/Telegram notifications for approvals
3. Reports and analytics dashboard
4. Barcode/QR code scanning for materials
5. Mobile app for field smiths
6. Material usage history and analytics
7. Automatic reorder suggestions based on usage patterns

## Permissions

The system uses role-based access control:
- **Super Admin**: Full access to everything
- **HR Manager**: Can approve and manage all smith records
- **Warehouse Manager**: Can approve and manage all smith records
- **Smith**: Can create and manage their own records (pending status)

## API Endpoints

If you need API access, you can create API routes for:
- GET /api/material-used
- POST /api/material-used
- GET /api/material-adjustments
- POST /api/smith-returns
- GET /api/smith-stock-issues

## Support

For issues or questions:
1. Check the policies to ensure proper role assignment
2. Verify migrations ran successfully
3. Ensure smith role exists in roles table
4. Check error logs in `storage/logs/laravel.log`

## File Structure

```
app/
├── Models/
│   ├── MaterialUsed.php
│   ├── MaterialAdjustment.php
│   ├── SmithReturn.php
│   └── SmithStockIssue.php
├── Policies/
│   ├── MaterialUsedPolicy.php
│   ├── MaterialAdjustmentPolicy.php
│   ├── SmithReturnPolicy.php
│   └── SmithStockIssuePolicy.php
└── Filament/
    └── Resources/
        ├── MaterialUsedResource.php
        ├── MaterialUsedResource/Pages/
        ├── MaterialAdjustmentResource.php
        ├── MaterialAdjustmentResource/Pages/
        ├── SmithReturnResource.php
        ├── SmithReturnResource/Pages/
        ├── SmithStockIssueResource.php
        └── SmithStockIssueResource/Pages/

database/
├── migrations/
│   ├── 2024_12_13_000001_create_material_used_table.php
│   ├── 2024_12_13_000002_create_material_adjustments_table.php
│   ├── 2024_12_13_000003_create_smith_returns_table.php
│   └── 2024_12_13_000004_create_smith_stock_issues_table.php
└── seeders/
    └── SmithRoleSeeder.php
```

## Notes

- All reference numbers are auto-generated and follow the pattern: PREFIX-YEAR-NUMBER
- Date fields default to current date
- Approval timestamps are automatically recorded
- Status changes are tracked
- All relationships use foreign key constraints for data integrity
