# 🎯 SAAT Absence Management System

A complete **Absence Management System** built with **Laravel 11** and **FilamentPHP 3**, featuring QR code-based attendance tracking, comprehensive user management, and real-time dashboard analytics.

![Laravel](https://img.shields.io/badge/Laravel-11.x-red)
![Filament](https://img.shields.io/badge/Filament-3.x-orange)
![PHP](https://img.shields.io/badge/PHP-8.2+-blue)
![License](https://img.shields.io/badge/License-MIT-green)

## 📋 Table of Contents

- [Features](#-features)
- [Screenshots](#-screenshots)
- [Installation](#-installation)
- [Usage](#-usage)
- [Database Structure](#-database-structure)
- [API Endpoints](#-api-endpoints)
- [Configuration](#-configuration)
- [Contributing](#-contributing)

## ✨ Features

### 👥 User Management
- ✅ Complete CRUD operations for employees
- ✅ Profile image upload with avatar fallback
- ✅ Comprehensive employee information (name, age, school, salary, KPA, etc.)
- ✅ Role-based organization
- ✅ Secure password management

### 🏢 Role & Department Management
- ✅ Create and manage roles/departments
- ✅ Assign roles to employees
- ✅ Track employee count per role
- ✅ Department descriptions and metadata

### 📱 QR Code Attendance System
- ✅ Dynamic QR code generation
- ✅ Automatic user detection
- ✅ Real-time check-in/check-out
- ✅ Duplicate entry prevention
- ✅ Single check-out per day validation
- ✅ Mobile-responsive scan interface
- ✅ Live clock display

### 📊 Dashboard & Analytics
- ✅ **Real-time Statistics**
  - Total employees
  - Present today
  - Still in office
  - Checked out count
- ✅ **Today's Attendance Table**
  - Live check-in/out times
  - Work hours calculation
  - Status indicators
- ✅ **Absent Employees Tracker**
  - Who's missing today
  - Contact information
  - Empty state handling

### 🔒 Security & Validation
- ✅ Policy-based authorization
- ✅ Custom validation rules
- ✅ CSRF protection
- ✅ Database-level constraints
- ✅ Secure password hashing

### 📤 Export & Reporting
- ✅ Excel export capability
- ✅ Filtered exports
- ✅ Bulk operations
- ✅ Date range filtering

## 🖼️ Screenshots

### Admin Dashboard
The dashboard provides an overview of attendance statistics and real-time data.

### QR Code Page
Display this QR code at your office entrance for easy scanning.

### Attendance Scan Interface
User-friendly interface for employees to check in/out.

### User Management
Comprehensive employee management with all necessary fields.

## 🚀 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL/MariaDB
- Node.js & NPM
- Git

### Step-by-Step Installation

1. **Clone the Repository**
```bash
cd d:\Intership_kess\SAAT-Attendance-System
```

2. **Install Dependencies**
```bash
# Install PHP dependencies
composer install

# Install QR code package
composer require simplesoftwareio/simple-qrcode

# Optional: Excel export
composer require pxlrbt/filament-excel
```

3. **Environment Setup**
```bash
# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate
```

4. **Configure Database**

Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saat_attendance
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. **Run Migrations**
```bash
php artisan migrate
```

6. **Seed Sample Data (Optional)**
```bash
php artisan db:seed --class=AttendanceSystemSeeder
```

This creates:
- 5 roles (Software Developer, HR Manager, Marketing Specialist, Sales Executive, Finance Officer)
- 7 sample users
- Sample attendance records
- Admin user: `admin@example.com` / `password`

7. **Create Filament Admin**
```bash
php artisan make:filament-user
```

8. **Storage Link**
```bash
php artisan storage:link
```

9. **Build Assets**
```bash
npm install
npm run build
```

10. **Start Server**
```bash
php artisan serve
```

## 💻 Usage

### Access Points

| Resource | URL | Description |
|----------|-----|-------------|
| **Admin Panel** | `http://localhost:8000/admin` | Filament dashboard |
| **QR Code** | `http://localhost:8000/attendance/qr` | Display QR code |
| **Scan Page** | `http://localhost:8000/attendance/scan` | Employee attendance |

### Admin Panel Features

1. **Dashboard** (`/admin`)
   - View attendance statistics
   - See today's attendance list
   - Track absent employees

2. **Users** (`/admin/users`)
   - Create/edit employees
   - Upload profile images
   - Assign roles
   - Set salary and KPA

3. **Roles** (`/admin/roles`)
   - Manage departments
   - Create new roles
   - View user count

4. **Attendances** (`/admin/attendances`)
   - View all records
   - Filter by date/role/user
   - Export to Excel
   - Calculate work hours

### Employee Workflow

1. **Check In**
   - Visit scan page or scan QR code
   - Select your name
   - Click "Check In"
   - System records time automatically

2. **Check Out**
   - Visit scan page again
   - Select your name
   - Click "Check Out"
   - System calculates work hours

### QR Code Setup

1. Navigate to `/attendance/qr`
2. Print or display on monitor/tablet
3. Employees scan to access attendance page
4. Automatic redirect to scan interface

## 🗄️ Database Structure

### Tables

#### `users`
- Personal info: name, age, school, email, phone
- Work info: role_id, salary, kpa
- Authentication: password, remember_token
- Media: profile_image

#### `roles`
- name, department, description
- Relationships to users and attendances

#### `attendances`
- user_id, role_id (foreign keys)
- date, time_in, time_out
- notes
- **Unique constraint**: (user_id, date)

### Relationships

```
User ─── belongs to ─── Role
 │
 └─── has many ─── Attendance

Role ─── has many ─── User
 │
 └─── has many ─── Attendance

Attendance ─── belongs to ─── User
 │
 └─── belongs to ─── Role
```

## 🔌 API Endpoints

### Attendance Routes

```php
GET  /attendance/qr            // Display QR code
GET  /attendance/scan          // Attendance scan page  
POST /attendance/process       // Process check-in/out
GET  /attendance/status        // Get attendance status
```

### Request/Response Examples

**Check In**
```json
POST /attendance/process
{
  "user_id": 1
}

Response:
{
  "success": true,
  "action": "check-in",
  "message": "Successfully checked in!",
  "time": "08:30:00",
  "user": "John Doe"
}
```

**Check Out**
```json
POST /attendance/process
{
  "user_id": 1
}

Response:
{
  "success": true,
  "action": "check-out",
  "message": "Successfully checked out!",
  "time": "17:00:00",
  "work_hours": "8h 30m",
  "user": "John Doe"
}
```

## ⚙️ Configuration

### Filament Configuration

Widgets are automatically discovered in `app/Filament/Widgets/`. To customize:

```php
// config/filament.php
'widgets' => [
    // Widget configuration
],
```

### Attendance Rules

Modify validation in `app/Http/Requests/AttendanceRequest.php`:

```php
public function rules(): array
{
    return [
        'user_id' => 'required|exists:users,id',
        'time_out' => 'nullable|after:time_in',
        // Add custom rules
    ];
}
```

### QR Code Customization

Edit `app/Http/Controllers/AttendanceController.php`:

```php
$qrCode = QrCode::size(300)
    ->backgroundColor(255, 255, 255)
    ->color(0, 0, 0)
    ->generate($url);
```

## 🛠️ Troubleshooting

### Common Issues

**QR Code not displaying**
```bash
composer require simplesoftwareio/simple-qrcode
php artisan config:clear
```

**Excel export not working**
```bash
composer require pxlrbt/filament-excel
php artisan filament:assets
```

**Images not uploading**
```bash
php artisan storage:link
chmod -R 775 storage/
```

**Widgets not showing**
```bash
php artisan config:clear
php artisan cache:clear
```

## 📦 File Structure

```
app/
├── Filament/
│   ├── Resources/
│   │   ├── UserResource.php
│   │   ├── RoleResource.php
│   │   └── AttendanceResource.php
│   └── Widgets/
│       ├── AttendanceStatsOverview.php
│       ├── TodayAttendanceTable.php
│       └── AbsentEmployeesTable.php
├── Http/
│   ├── Controllers/
│   │   └── AttendanceController.php
│   └── Requests/
│       └── AttendanceRequest.php
├── Models/
│   ├── User.php
│   ├── Role.php
│   └── Attendance.php
└── Policies/
    └── AttendancePolicy.php

database/
├── migrations/
│   ├── 2024_01_01_000003_create_roles_table.php
│   ├── 2024_01_01_000004_add_fields_to_users_table.php
│   └── 2024_01_01_000005_create_attendances_table.php
└── seeders/
    └── AttendanceSystemSeeder.php

resources/
└── views/
    └── attendance/
        ├── qr-code.blade.php
        └── scan.blade.php
```

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## 📝 License

This project is open-sourced under the MIT License.

## 👨‍💻 Credits

Built with:
- [Laravel](https://laravel.com) - PHP Framework
- [FilamentPHP](https://filamentphp.com) - Admin Panel
- [SimpleSoftwareIO QR Code](https://github.com/SimpleSoftwareIO/simple-qrcode) - QR Generation
- [Tailwind CSS](https://tailwindcss.com) - Styling

## 📞 Support

For support, please:
1. Check the [INSTALLATION.md](INSTALLATION.md) guide
2. Review Laravel docs: https://laravel.com/docs
3. Review Filament docs: https://filamentphp.com/docs
4. Open an issue on GitHub

---

Made with ❤️ for efficient attendance management
