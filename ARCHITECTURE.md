# SAAT Attendance System - Architecture & Flow Diagrams

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         FRONTEND LAYER                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  │
│  │  Filament Admin  │  │   QR Code Page   │  │   Scan Page      │  │
│  │   Dashboard      │  │   (Display)      │  │  (Check-in/out)  │  │
│  └────────┬─────────┘  └────────┬─────────┘  └────────┬─────────┘  │
│           │                     │                     │            │
│           │                     │                     │            │
└───────────┼─────────────────────┼─────────────────────┼────────────┘
            │                     │                     │
            ▼                     ▼                     ▼
┌─────────────────────────────────────────────────────────────────────┐
│                      APPLICATION LAYER                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐  │
│  │ Filament         │  │  Attendance      │  │   Routes &       │  │
│  │ Resources        │  │  Controller      │  │   Middleware     │  │
│  │ (CRUD)           │  │  (Logic)         │  │   (Auth)         │  │
│  └────────┬─────────┘  └────────┬─────────┘  └────────┬─────────┘  │
│           │                     │                     │            │
│           ▼                     ▼                     ▼            │
│  ┌──────────────────────────────────────────────────────────────┐  │
│  │              Models (User, Role, Attendance)                 │  │
│  │         Policies, Requests, Validation Rules                 │  │
│  └────────────────────────────┬─────────────────────────────────┘  │
│                               │                                    │
└───────────────────────────────┼────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         DATABASE LAYER                              │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────────┐     │
│  │    users     │◄───│    roles     │◄───│   attendances    │     │
│  │              │    │              │    │                  │     │
│  │ • name       │    │ • name       │    │ • user_id (FK)   │     │
│  │ • email      │    │ • department │    │ • role_id (FK)   │     │
│  │ • age        │    │ • description│    │ • date           │     │
│  │ • role_id    │    │              │    │ • time_in        │     │
│  │ • salary     │    │              │    │ • time_out       │     │
│  │ • kpa        │    │              │    │                  │     │
│  └──────────────┘    └──────────────┘    └──────────────────┘     │
│                                                                     │
│  Constraint: UNIQUE(user_id, date) on attendances                  │
└─────────────────────────────────────────────────────────────────────┘
```

## Attendance Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                    ATTENDANCE PROCESS FLOW                          │
└─────────────────────────────────────────────────────────────────────┘

┌──────────┐
│  START   │
└────┬─────┘
     │
     ▼
┌────────────────────┐
│ User scans QR code │
│  or visits URL     │
└────┬───────────────┘
     │
     ▼
┌────────────────────┐
│ Select user name   │
│  from dropdown     │
└────┬───────────────┘
     │
     ▼
┌────────────────────────────┐
│ Click attendance button    │
└────┬───────────────────────┘
     │
     ▼
┌─────────────────────────────────────────┐
│ System checks: Does user have record    │
│ for today's date?                       │
└────┬────────────────────────┬───────────┘
     │                        │
     │ NO                     │ YES
     │                        │
     ▼                        ▼
┌────────────────┐    ┌──────────────────────┐
│  CHECK-IN      │    │ Has time_out value?  │
│                │    └──┬───────────────┬───┘
│ Create new     │       │               │
│ attendance:    │       │ NO            │ YES
│ • user_id      │       │               │
│ • role_id      │       ▼               ▼
│ • date (today) │    ┌──────────┐   ┌─────────────┐
│ • time_in(now) │    │CHECK-OUT │   │   ERROR     │
│ • time_out=null│    │          │   │             │
└────┬───────────┘    │ Update:  │   │ "Already    │
     │                │ • time_out│   │  checked    │
     │                │          │   │  out today" │
     │                │ Calculate│   └─────────────┘
     │                │ work hrs │
     │                └────┬─────┘
     │                     │
     ▼                     ▼
┌─────────────────────────────────┐
│   Display success message       │
│   • User name                   │
│   • Action (check-in/out)       │
│   • Time                        │
│   • Work hours (if check-out)   │
└─────┬───────────────────────────┘
      │
      ▼
┌─────────────┐
│     END     │
└─────────────┘
```

## Dashboard Widget Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                    DASHBOARD DISPLAY LOGIC                          │
└─────────────────────────────────────────────────────────────────────┘

ADMIN ACCESSES DASHBOARD
         │
         ▼
┌────────────────────────────────────────┐
│  AttendanceStatsOverview Widget        │
├────────────────────────────────────────┤
│  Query Database:                       │
│  • Total Users: COUNT(users)           │
│  • Present: COUNT(attendances today)   │
│  • Still in: COUNT(time_out IS NULL)   │
│  • Checked out: COUNT(time_out NOT NULL)│
│  • Absent: Total - Present             │
└────────────────────────────────────────┘
         │
         ▼
┌────────────────────────────────────────┐
│  TodayAttendanceTable Widget           │
├────────────────────────────────────────┤
│  Query: attendances WHERE date=today   │
│  Display:                              │
│  • User name                           │
│  • Role                                │
│  • Time in                             │
│  • Time out                            │
│  • Status (In Office / Checked Out)    │
│  • Work hours calculation              │
└────────────────────────────────────────┘
         │
         ▼
┌────────────────────────────────────────┐
│  AbsentEmployeesTable Widget           │
├────────────────────────────────────────┤
│  Query:                                │
│  • Get all users                       │
│  • Exclude users in today's attendance │
│  Display absent employees with:        │
│  • Name, Role, Phone, Email            │
│  • "Absent" badge                      │
└────────────────────────────────────────┘
```

## Data Relationships Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                    MODEL RELATIONSHIPS                              │
└─────────────────────────────────────────────────────────────────────┘

        ┌──────────────┐
        │     ROLE     │
        │ (Department) │
        └───┬──────┬───┘
            │      │
      hasMany│     │hasMany
            │      │
    ┌───────▼──┐   └─────────┐
    │          │             │
    │   USER   │             │
    │          │             │
    └────┬─────┘             │
         │                   │
         │hasMany            │
         │                   │
         ▼                   ▼
    ┌──────────────────────────┐
    │      ATTENDANCE          │
    │                          │
    │  belongsTo User          │
    │  belongsTo Role          │
    └──────────────────────────┘

Cascade Rules:
• Delete User → Delete Attendances (CASCADE)
• Delete Role → Set NULL on User.role_id (SET NULL)
• Delete Role → Set NULL on Attendance.role_id (SET NULL)
```

## Security & Validation Flow

```
┌─────────────────────────────────────────────────────────────────────┐
│                  SECURITY & VALIDATION LAYERS                       │
└─────────────────────────────────────────────────────────────────────┘

USER REQUEST
     │
     ▼
┌─────────────────┐
│ CSRF Protection │ ← Laravel Middleware
└────┬────────────┘
     │
     ▼
┌─────────────────┐
│ Route Middleware│ ← Check if user is authenticated (optional)
└────┬────────────┘
     │
     ▼
┌──────────────────────┐
│ Controller Method    │
└────┬─────────────────┘
     │
     ▼
┌──────────────────────┐
│ Request Validation   │ ← AttendanceRequest
│                      │   • Validate user_id exists
│ • Check required     │   • Validate date format
│ • Check formats      │   • Check time_out > time_in
│ • Custom rules       │   • Prevent duplicates
└────┬─────────────────┘
     │
     ▼
┌──────────────────────┐
│ Policy Check         │ ← AttendancePolicy
│                      │   • Can user create?
│ • Check permissions  │   • Can user update?
│ • User ownership     │   • Can user delete?
└────┬─────────────────┘
     │
     ▼
┌──────────────────────┐
│ Business Logic       │
│                      │
│ • Check duplicates   │
│ • Prevent double     │
│   check-in/out       │
└────┬─────────────────┘
     │
     ▼
┌──────────────────────┐
│ Database Constraints │
│                      │
│ • UNIQUE(user_id,    │
│   date)              │
│ • Foreign keys       │
│ • NOT NULL fields    │
└────┬─────────────────┘
     │
     ▼
┌──────────────────────┐
│ Success Response     │
└──────────────────────┘
```

## File Organization Structure

```
SAAT-Attendance-System/
│
├── app/
│   ├── Filament/
│   │   ├── Resources/
│   │   │   ├── UserResource.php ──────────────┐
│   │   │   ├── RoleResource.php              │ Admin Panel
│   │   │   ├── AttendanceResource.php        │ Management
│   │   │   └── [Resource]/Pages/  ───────────┘
│   │   │
│   │   └── Widgets/
│   │       ├── AttendanceStatsOverview.php ───┐
│   │       ├── TodayAttendanceTable.php       │ Dashboard
│   │       └── AbsentEmployeesTable.php ──────┘
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── AttendanceController.php ──────── QR & Scan Logic
│   │   │
│   │   └── Requests/
│   │       └── AttendanceRequest.php ─────────── Validation
│   │
│   ├── Models/
│   │   ├── User.php ─────────────────────────┐
│   │   ├── Role.php                          │ Data Models
│   │   └── Attendance.php ───────────────────┘
│   │
│   └── Policies/
│       └── AttendancePolicy.php ──────────────── Authorization
│
├── database/
│   ├── migrations/
│   │   ├── *_create_roles_table.php
│   │   ├── *_add_fields_to_users_table.php
│   │   └── *_create_attendances_table.php
│   │
│   └── seeders/
│       └── AttendanceSystemSeeder.php ─────────── Sample Data
│
├── resources/
│   └── views/
│       └── attendance/
│           ├── qr-code.blade.php ──────────────┐ User-facing
│           └── scan.blade.php ─────────────────┘ Views
│
└── routes/
    └── web.php ───────────────────────────────── Attendance Routes
```

## Export Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                      EXPORT TO EXCEL FLOW                           │
└─────────────────────────────────────────────────────────────────────┘

ADMIN SELECTS RECORDS
        │
        ▼
┌────────────────────┐
│ Clicks Export      │
│ Bulk Action        │
└────┬───────────────┘
     │
     ▼
┌─────────────────────────┐
│ FilamentExcel Package   │
│ processes records       │
└────┬────────────────────┘
     │
     ▼
┌─────────────────────────┐
│ Generates Excel file    │
│ with columns:           │
│ • User Name             │
│ • Role                  │
│ • Date                  │
│ • Time In               │
│ • Time Out              │
│ • Work Hours            │
└────┬────────────────────┘
     │
     ▼
┌─────────────────────────┐
│ Download to browser     │
└─────────────────────────┘
```

## Real-time Status Updates

```
┌─────────────────────────────────────────────────────────────────────┐
│                   SCAN PAGE REAL-TIME UPDATES                       │
└─────────────────────────────────────────────────────────────────────┘

Page Load
    │
    ▼
┌──────────────────┐
│ JavaScript clock │ ← Updates every 1 second
│ starts ticking   │   Shows current time
└────┬─────────────┘
     │
     │ User selects name
     │
     ▼
┌──────────────────────┐
│ AJAX Request to      │
│ /attendance/status   │
└────┬─────────────────┘
     │
     ▼
┌────────────────────────────┐
│ Check today's attendance   │
└────┬──────────────┬────────┘
     │              │
     │ Found        │ Not Found
     │              │
     ▼              ▼
Display:         Display:
• Time in        • "Check In" button
• Time out       • No status
• "Check Out"
  button

User clicks button
     │
     ▼
AJAX POST to /attendance/process
     │
     ▼
Success → Refresh status display
```

---

**Legend:**
- `│ ─ ┌ └ ┐ ┘` : Box drawing characters
- `▼ ►` : Flow direction
- `◄ ○ ●` : Connection points
- `FK` : Foreign Key
