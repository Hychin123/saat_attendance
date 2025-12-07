# Shift / Work Schedule Feature

## Overview
The Shift/Work Schedule feature allows you to manage employee work schedules, assign shifts to users, and automatically track attendance based on shift timings.

## Features

### 1. Shift Management
- Create and manage multiple shifts (Morning, Afternoon, Night, etc.)
- Configure shift timings (start time, end time)
- Set grace periods for late arrivals
- Define minimum work hours
- Support for overnight shifts (spanning midnight)
- Specify working days for each shift
- Color coding for visual identification
- Activate/deactivate shifts as needed

### 2. User-Shift Assignment
- Assign multiple shifts to users
- Set effective dates for shift assignments (from/to)
- Mark primary shift for each user
- Track shift history with date ranges

### 3. Attendance Integration
- Automatic late detection based on shift grace period
- Calculate late minutes
- Track actual work hours
- Link attendance records to shifts

## Database Structure

### Tables Created

1. **shifts**
   - `id`: Primary key
   - `name`: Shift name (e.g., "Morning Shift")
   - `code`: Unique shift code (e.g., "MS")
   - `start_time`: Shift start time
   - `end_time`: Shift end time
   - `grace_period_minutes`: Late tolerance (default: 15 minutes)
   - `minimum_work_hours`: Required work hours (default: 8)
   - `is_active`: Active status
   - `is_overnight`: Overnight shift flag
   - `description`: Optional description
   - `working_days`: JSON array of working days
   - `color`: Hex color for UI display

2. **user_shifts** (pivot table)
   - `id`: Primary key
   - `user_id`: Foreign key to users
   - `shift_id`: Foreign key to shifts
   - `effective_from`: Assignment start date
   - `effective_to`: Assignment end date (nullable)
   - `is_primary`: Primary shift flag

3. **attendances** (updated)
   - Added `shift_id`: Foreign key to shifts
   - Added `is_late`: Late status flag
   - Added `late_minutes`: Minutes late
   - Added `work_hours`: Actual work hours

## Usage

### Creating a Shift

1. Navigate to **Shifts** in the sidebar
2. Click **Create**
3. Fill in shift details:
   - Name and Code
   - Start and End times
   - Grace period and minimum work hours
   - Select working days
   - Toggle overnight if needed
   - Choose a display color
4. Click **Save**

### Assigning Shifts to Users

1. Navigate to **Users**
2. Edit a user
3. Expand the **Shift Assignment** section
4. Click **Add Shift Assignment**
5. Fill in:
   - Select the shift
   - Set effective from date
   - Optionally set effective to date
   - Mark as primary if needed
6. Click **Save**

### Sample Shifts Included

After running the seeder, you'll have these shifts:

1. **Morning Shift (MS)**: 8:00 AM - 4:00 PM (Mon-Fri)
2. **Afternoon Shift (AS)**: 2:00 PM - 10:00 PM (Mon-Fri)
3. **Night Shift (NS)**: 10:00 PM - 6:00 AM (Mon-Fri, Overnight)
4. **Day Shift (DS)**: 9:00 AM - 5:00 PM (Mon-Fri)
5. **Weekend Shift (WS)**: 8:00 AM - 4:00 PM (Sat-Sun)

## Models & Relationships

### Shift Model
```php
// Get users assigned to this shift
$shift->users

// Check if user is late
$shift->isLate($checkInTime)

// Get late minutes
$shift->getLateMinutes($checkInTime)

// Check if active on a specific day
$shift->isActiveOnDay('monday')

// Get shift duration
$shift->getDurationInHours()
```

### User Model
```php
// Get all shifts for a user
$user->shifts

// Get current active shift
$user->getCurrentShift()

// Get all active shifts on a date
$user->getActiveShifts($date)
```

### Attendance Model
```php
// Calculate work hours
$attendance->calculateWorkHours()

// Check if late based on shift
$attendance->checkIfLate()

// Automatic calculation on save
// - work_hours calculated when both time_in and time_out exist
// - is_late and late_minutes calculated when shift is assigned
```

## Permissions

Shift management and assignment features are visible to:
- Super Admins
- HR Managers

Regular users can view their assigned shifts but cannot modify them.

## API Methods

### Get User's Current Shift
```php
$currentShift = auth()->user()->getCurrentShift();
if ($currentShift) {
    echo "Current shift: {$currentShift->name}";
}
```

### Check Late Status
```php
$attendance = Attendance::find($id);
if ($attendance->is_late) {
    echo "Late by {$attendance->late_minutes} minutes";
}
```

### Filter Active Shifts
```php
$activeShifts = Shift::active()->get();
```

## Future Enhancements

Potential features to add:
- Shift swap requests
- Shift rotation scheduling
- Break time tracking
- Overtime calculation
- Shift reports and analytics
- Mobile notifications for shift changes
- Shift conflict detection
- Bulk shift assignment
