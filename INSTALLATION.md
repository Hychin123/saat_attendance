# Absence Management System - Installation Guide

## Overview
Complete Absence Management System built with Laravel 11 and FilamentPHP 3, featuring QR code attendance tracking, role management, and comprehensive dashboard widgets.

## Features Implemented

### ✅ 1. User Management
- Complete CRUD operations for users
- Fields: name, age, school, role, salary, KPA, phone, email, profile image
- Profile image upload with avatar display
- Role assignment
- Password management

### ✅ 2. Role Management
- Create and manage roles/departments
- Assign roles to users
- Track users per role

### ✅ 3. QR Code Attendance System
- QR code generation for attendance page
- Automatic check-in/check-out detection
- Prevents duplicate check-ins on the same day
- Single check-out per day validation
- Real-time status updates
- User-friendly scan interface

### ✅ 4. Attendance Tracking
- Complete attendance records
- Track time in/out
- Calculate work hours
- Filter by date, role, user
- Prevent duplicate entries with database constraint

### ✅ 5. Dashboard Widgets
- **Stats Overview**: Total employees, present today, still in office, checked out
- **Today's Attendance Table**: Real-time list of check-ins/outs
- **Absent Employees Table**: Track who hasn't checked in
- Export capabilities

### ✅ 6. Advanced Features
- Policy-based authorization
- Custom validation rules
- Export to Excel (ready)
- Responsive UI
- Real-time clock

## Installation Steps

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL/MariaDB
- Node.js & NPM

### Step 1: Install Required Composer Packages

```bash
# Navigate to your project directory
cd d:\Intership_kess\SAAT-Attendance-System

# Install QR Code package
composer require simplesoftwareio/simple-qrcode

# Install Filament Excel Export (optional but recommended)
composer require pxlrbt/filament-excel

# Install all dependencies
composer install
```

### Step 2: Environment Configuration

```bash
# Copy .env.example to .env if not already done
copy .env.example .env

# Generate application key
php artisan key:generate
```

Configure your `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saat_attendance
DB_USERNAME=root
DB_PASSWORD=your_password

APP_URL=http://localhost:8000
```

### Step 3: Database Setup

```bash
# Create database migrations
php artisan migrate

# Optional: Seed with sample data
php artisan db:seed
```

### Step 4: Create Filament Admin User

```bash
php artisan make:filament-user
```

Follow the prompts to create your first admin user.

### Step 5: Storage Setup

```bash
# Create symbolic link for file storage
php artisan storage:link
```

### Step 6: Install Frontend Dependencies

```bash
npm install
npm run build
```

### Step 7: Start Development Server

```bash
php artisan serve
```

## Usage

### Access Points

1. **Filament Admin Panel**: `http://localhost:8000/admin`
   - Login with your admin credentials
   - Manage users, roles, and attendance records
   - View dashboard widgets

2. **QR Code**: `http://localhost:8000/attendance/qr`
   - Display QR code for attendance scanning
   - Print or display on monitor

3. **Attendance Scan Page**: `http://localhost:8000/attendance/scan`
   - User-facing attendance interface
   - Select name and check in/out

## Database Schema

### Users Table
- id, name, email, password
- age, school, phone
- role_id (foreign key)
- salary, kpa
- profile_image
- timestamps

### Roles Table
- id, name, department, description
- timestamps

### Attendances Table
- id, user_id, role_id
- date, time_in, time_out
- notes
- timestamps
- **Unique constraint**: (user_id, date)

## API Endpoints

### Attendance Routes
- `GET /attendance/qr` - Display QR code
- `GET /attendance/scan` - Attendance scan page
- `POST /attendance/process` - Process check-in/out
- `GET /attendance/status` - Get user attendance status

## Validation Rules

### Attendance Check-in/out
- User can only check in once per day
- User can only check out if already checked in
- Cannot check out twice
- Time out must be after time in
- Automatic role assignment from user profile

## Filament Resources

### UserResource
- List, create, edit, delete users
- Upload profile images
- Manage all user fields
- Filter by role
- Avatar display with fallback

### RoleResource
- Manage departments/roles
- View user count per role
- CRUD operations

### AttendanceResource
- View all attendance records
- Filter by date range, role, user
- See work hours calculated
- Export to Excel
- Identify incomplete check-outs

## Dashboard Widgets

### AttendanceStatsOverview
- Total employees count
- Present today count
- Still in office count
- Checked out count

### TodayAttendanceTable
- Real-time today's attendance
- Shows check-in/out times
- Calculates work hours
- Status badges

### AbsentEmployeesTable
- Lists employees who haven't checked in
- Shows contact information
- Empty state when everyone is present

## Additional Features

### QR Code Generation
- Automatic QR code generation
- Links to attendance scan page
- Modern, printable design

### Attendance Validation
- Database-level unique constraint
- Application-level validation
- Policy-based authorization
- Prevents duplicate entries

### Export Functionality
- Excel export ready (requires pxlrbt/filament-excel)
- Bulk export from attendance table
- Filtered exports

## Customization

### Adding More Fields
1. Create migration: `php artisan make:migration add_field_to_table`
2. Update model's `$fillable` array
3. Add to Filament resource form and table

### Custom Widgets
1. Create widget: `php artisan make:filament-widget WidgetName`
2. Implement in `app/Filament/Widgets/`
3. Automatically appears on dashboard

### Modify Attendance Logic
- Edit `app/Http/Controllers/AttendanceController.php`
- Update validation in `app/Http/Requests/AttendanceRequest.php`
- Adjust policy in `app/Policies/AttendancePolicy.php`

## Troubleshooting

### QR Code Not Displaying
```bash
composer require simplesoftwareio/simple-qrcode
php artisan config:clear
```

### Excel Export Not Working
```bash
composer require pxlrbt/filament-excel
php artisan filament:assets
```

### Profile Images Not Uploading
```bash
php artisan storage:link
# Check storage/app/public/ permissions
```

### Widgets Not Showing
- Verify widget classes are in `app/Filament/Widgets/`
- Clear cache: `php artisan config:clear`
- Check Filament config: `config/filament.php`

## Security Considerations

- All routes use CSRF protection
- Password hashing via Laravel's Hash facade
- Policy-based authorization
- Database constraints prevent duplicate entries
- Input validation on all forms

## Future Enhancements

- [ ] Email notifications for absences
- [ ] SMS integration for late arrivals
- [ ] Biometric integration
- [ ] Geolocation tracking
- [ ] Leave request management
- [ ] Overtime calculation
- [ ] Multi-tenant support
- [ ] Mobile app integration

## Support

For issues or questions:
1. Check Laravel documentation: https://laravel.com/docs
2. Check Filament documentation: https://filamentphp.com/docs
3. Review migration files in `database/migrations/`
4. Check error logs in `storage/logs/`

## License

This project is built with open-source technologies:
- Laravel Framework (MIT License)
- FilamentPHP (MIT License)
- SimpleSoftwareIO QR Code (MIT License)
