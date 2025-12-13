# Smith Management Permissions

## Overview
Added comprehensive permission system for Smith Management module.

## Permissions Created

### Resources (4)
1. **material_used** - Material usage tracking
2. **material_adjustments** - Material quantity adjustments
3. **smith_returns** - Defective material returns
4. **smith_stock_issues** - Material stock requests

### Actions per Resource (4)
- **view** - View records
- **create** - Create new records
- **edit** - Edit existing records
- **delete** - Delete records

### Total Permissions: 16
(4 resources × 4 actions = 16 permissions)

## RoleResource Updates

### Added "Smith Management" Tab
New permission tab in Role management under the Permissions section with:
- Material Used permissions
- Material Adjustments permissions
- Smith Returns permissions
- Smith Stock Issues permissions

### Added "Production" Department
- Added to department dropdown options
- Configured badge colors (info/blue)
- Added wrench-screwdriver icon for Production department
- Available in filters

## Files Created/Modified

### New Files
- `database/seeders/SmithPermissionsSeeder.php` - Seeds smith permissions

### Modified Files
- `app/Filament/Resources/RoleResource.php` - Added Production department and Smith Management tab
- `database/seeders/DatabaseSeeder.php` - Added SmithPermissionsSeeder to seed chain

## Usage

### Assign Permissions to Role
1. Go to User Management → Roles
2. Edit or create a role
3. Open "Permissions" section
4. Navigate to "Smith Management" tab
5. Select desired permissions for each resource
6. Save the role

### Permission Examples

**Smith Role** (typical permissions):
- ✅ View Material Used
- ✅ Create Material Used
- ✅ Edit Material Used (own records)
- ✅ View Smith Stock Issues
- ✅ Create Smith Stock Issues
- ✅ View Smith Returns
- ✅ Create Smith Returns

**Warehouse Manager Role** (typical permissions):
- ✅ View all smith management resources
- ✅ Create all smith management resources
- ✅ Edit all smith management resources
- ✅ Delete all smith management resources

**HR Manager Role** (typical permissions):
- ✅ View all smith management resources
- ✅ Edit all smith management resources (for approvals)

## Permission Enforcement

Permissions work alongside policies for access control:
- **Policies** define role-based access (Smith, HR Manager, Warehouse Manager)
- **Permissions** provide fine-grained control within roles
- Both systems work together for comprehensive security

## Seeding

Run the seeder to create permissions:
```bash
php artisan db:seed --class=SmithPermissionsSeeder
```

Or run all seeders:
```bash
php artisan db:seed
```

## Database Structure

Permissions are stored in the `permissions` table with:
- `name` - Action (view, create, edit, delete)
- `resource` - Resource name (material_used, etc.)
- `display_name` - Human-readable name
- `description` - Permission description

Role-Permission relationships stored in `role_permissions` pivot table.
