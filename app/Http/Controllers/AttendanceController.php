<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    /**
     * Display the QR code for attendance.
     */
    public function showQrCode()
    {
        $url = route('attendance.scan');
        $qrCode = QrCode::size(300)->generate($url);
        
        return view('attendance.qr-code', compact('qrCode'));
    }

    /**
     * Show the attendance scan page.
     */
    public function showScanPage()
    {
        $user = Auth::user();
        $todayAttendance = Attendance::getTodayAttendance($user->id);
        
        return view('attendance.scan', compact('user', 'todayAttendance'));
    }

    /**
     * Process attendance check-in or check-out.
     */
    public function processAttendance(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        $user = User::with('role')->findOrFail($userId);
        $today = Carbon::today();

        // Check if user already has an attendance record for today
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            // Check-in
            $attendance = Attendance::create([
                'user_id' => $userId,
                'role_id' => $user->role_id,
                'date' => $today,
                'time_in' => now(),
            ]);

            return response()->json([
                'success' => true,
                'action' => 'check-in',
                'message' => 'Successfully checked in!',
                'time' => $attendance->time_in->format('H:i:s'),
                'user' => $user->name,
            ]);
        } elseif ($attendance && !$attendance->time_out) {
            // Check-out
            $attendance->update([
                'time_out' => now(),
            ]);

            $workHours = $attendance->time_in->diffInMinutes($attendance->time_out);
            $hours = floor($workHours / 60);
            $minutes = $workHours % 60;

            return response()->json([
                'success' => true,
                'action' => 'check-out',
                'message' => 'Successfully checked out!',
                'time' => $attendance->time_out->format('H:i:s'),
                'work_hours' => "{$hours}h {$minutes}m",
                'user' => $user->name,
            ]);
        } else {
            // Already checked out
            return response()->json([
                'success' => false,
                'message' => 'You have already checked out for today.',
            ], 400);
        }
    }

    /**
     * Get attendance status for a user.
     */
    public function getStatus(Request $request)
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $attendance = Attendance::getTodayAttendance($userId);
        $user = User::find($userId);

        if ($attendance) {
            $status = 'checked-in';
            if ($attendance->time_out) {
                $status = 'checked-out';
            }

            return response()->json([
                'success' => true,
                'status' => $status,
                'attendance' => [
                    'time_in' => $attendance->time_in->format('H:i:s'),
                    'time_out' => $attendance->time_out?->format('H:i:s'),
                    'user' => $user->name,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => 'not-checked-in',
        ]);
    }
}
