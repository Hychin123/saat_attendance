<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    /**
     * Process chatbot query and return response
     */
    public function query(Request $request)
    {
        $query = strtolower(trim($request->input('query', '')));
        $user = Auth::user();

        if (empty($query)) {
            return response()->json([
                'success' => false,
                'message' => 'Please ask me something about attendance!'
            ]);
        }

        // Process the query and generate response
        $response = $this->processQuery($query, $user);

        return response()->json([
            'success' => true,
            'message' => $response['message'],
            'data' => $response['data'] ?? null,
            'type' => $response['type'] ?? 'text'
        ]);
    }

    /**
     * Process natural language query
     */
    private function processQuery(string $query, User $user): array
    {
        // My attendance queries
        if ($this->matchesPattern($query, ['my attendance', 'my records', 'my time'])) {
            return $this->getMyAttendance($query, $user);
        }

        // Today's status
        if ($this->matchesPattern($query, ['today', 'checked in', 'checked out', 'am i late'])) {
            return $this->getTodayStatus($user);
        }

        // Absent employees (admin/manager only)
        if ($this->matchesPattern($query, ['who is absent', 'absent today', 'who\'s absent', 'missing today'])) {
            return $this->getAbsentEmployees($user);
        }

        // Late employees
        if ($this->matchesPattern($query, ['who is late', 'late today', 'who\'s late'])) {
            return $this->getLateEmployees($user);
        }

        // Statistics
        if ($this->matchesPattern($query, ['statistics', 'stats', 'summary', 'overview'])) {
            return $this->getStatistics($query, $user);
        }

        // Work hours
        if ($this->matchesPattern($query, ['work hours', 'hours worked', 'total hours', 'how many hours'])) {
            return $this->getWorkHours($query, $user);
        }

        // Specific user query (admin/manager only)
        if ($this->matchesPattern($query, ['attendance for', 'show attendance of', 'check attendance'])) {
            return $this->getUserAttendance($query, $user);
        }

        // Help command
        if ($this->matchesPattern($query, ['help', 'what can you do', 'commands'])) {
            return $this->getHelp();
        }

        // Default response if no pattern matches
        return [
            'type' => 'text',
            'message' => "I'm not sure I understand. Try asking:\n" .
                        "• 'Show my attendance this month'\n" .
                        "• 'Am I late today?'\n" .
                        "• 'Who is absent today?'\n" .
                        "• 'Show my work hours this week'\n" .
                        "• Type 'help' for more commands"
        ];
    }

    /**
     * Check if query matches any pattern
     */
    private function matchesPattern(string $query, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (str_contains($query, $pattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get my attendance records
     */
    private function getMyAttendance(string $query, User $user): array
    {
        $period = $this->extractPeriod($query);
        
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$period['start'], $period['end']])
            ->orderBy('date', 'desc')
            ->get();

        $totalDays = $attendances->count();
        $lateDays = $attendances->filter(fn($a) => $a->is_late)->count();
        $totalHours = $attendances->sum(fn($a) => $a->work_hours ?? 0);

        $message = "📊 Your Attendance ({$period['label']}):\n\n" .
                   "✅ Days Present: {$totalDays}\n" .
                   "⏰ Late Days: {$lateDays}\n" .
                   "🕐 Total Hours: " . round($totalHours, 1) . "h\n";

        if ($totalDays > 0) {
            $message .= "\nRecent records:\n";
            foreach ($attendances->take(5) as $attendance) {
                $status = $attendance->is_late ? '⚠️ Late' : '✅ On Time';
                $hours = $attendance->work_hours ? round($attendance->work_hours, 1) . 'h' : 'In progress';
                $message .= "• {$attendance->date->format('M d')}: {$status} - {$hours}\n";
            }
        }

        return [
            'type' => 'attendance_list',
            'message' => $message,
            'data' => $attendances->take(10)->values()
        ];
    }

    /**
     * Get today's status
     */
    private function getTodayStatus(User $user): array
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', Carbon::today())
            ->first();

        if (!$attendance) {
            return [
                'type' => 'text',
                'message' => "❌ You haven't checked in today yet!\n\nPlease use the QR scanner to check in."
            ];
        }

        $status = $attendance->is_late ? '⚠️ Late' : '✅ On Time';
        $timeIn = $attendance->time_in->format('h:i A');
        
        $message = "📅 Today's Status:\n\n" .
                   "Status: {$status}\n" .
                   "⏰ Checked In: {$timeIn}\n";

        if ($attendance->time_out) {
            $timeOut = $attendance->time_out->format('h:i A');
            $hours = round($attendance->work_hours ?? 0, 1);
            $message .= "🏁 Checked Out: {$timeOut}\n" .
                       "🕐 Work Hours: {$hours}h";
        } else {
            $currentHours = $attendance->time_in->diffInMinutes(now()) / 60;
            $message .= "🕐 Current Hours: " . round($currentHours, 1) . "h\n" .
                       "⚡ Still working...";
        }

        if ($attendance->is_late && $attendance->late_minutes) {
            $message .= "\n\n⚠️ You were {$attendance->late_minutes} minutes late.";
        }

        return [
            'type' => 'status',
            'message' => $message,
            'data' => $attendance
        ];
    }

    /**
     * Get absent employees (admin/manager only)
     */
    private function getAbsentEmployees(User $user): array
    {
        if (!$this->canViewAllAttendance($user)) {
            return [
                'type' => 'text',
                'message' => "🔒 You don't have permission to view other employees' attendance."
            ];
        }

        $today = Carbon::today();
        $allUsers = User::where('id', '!=', $user->id)->get();
        $presentUserIds = Attendance::whereDate('date', $today)
            ->pluck('user_id')
            ->toArray();

        $absentUsers = $allUsers->whereNotIn('id', $presentUserIds);

        if ($absentUsers->isEmpty()) {
            return [
                'type' => 'text',
                'message' => "🎉 Great! Everyone is present today!"
            ];
        }

        $message = "📋 Absent Today (" . $absentUsers->count() . "):\n\n";
        foreach ($absentUsers as $absentUser) {
            $role = $absentUser->role ? $absentUser->role->name : 'No Role';
            $message .= "• {$absentUser->name} ({$role})\n";
        }

        return [
            'type' => 'user_list',
            'message' => $message,
            'data' => $absentUsers->values()
        ];
    }

    /**
     * Get late employees
     */
    private function getLateEmployees(User $user): array
    {
        if (!$this->canViewAllAttendance($user)) {
            return [
                'type' => 'text',
                'message' => "🔒 You don't have permission to view other employees' attendance."
            ];
        }

        $lateAttendances = Attendance::with('user')
            ->whereDate('date', Carbon::today())
            ->where('is_late', true)
            ->get();

        if ($lateAttendances->isEmpty()) {
            return [
                'type' => 'text',
                'message' => "✅ No one is late today!"
            ];
        }

        $message = "⚠️ Late Today (" . $lateAttendances->count() . "):\n\n";
        foreach ($lateAttendances as $attendance) {
            $timeIn = $attendance->time_in->format('h:i A');
            $message .= "• {$attendance->user->name} - {$timeIn} ({$attendance->late_minutes}min late)\n";
        }

        return [
            'type' => 'attendance_list',
            'message' => $message,
            'data' => $lateAttendances->values()
        ];
    }

    /**
     * Get statistics
     */
    private function getStatistics(string $query, User $user): array
    {
        $period = $this->extractPeriod($query);
        
        if ($this->canViewAllAttendance($user)) {
            // Admin/Manager statistics
            $totalUsers = User::count();
            $attendances = Attendance::whereBetween('date', [$period['start'], $period['end']])->get();
            $totalAttendances = $attendances->count();
            $lateCount = $attendances->where('is_late', true)->count();
            $avgWorkHours = $attendances->avg('work_hours') ?? 0;

            $message = "📊 Overall Statistics ({$period['label']}):\n\n" .
                       "👥 Total Employees: {$totalUsers}\n" .
                       "✅ Total Attendances: {$totalAttendances}\n" .
                       "⚠️ Late Check-ins: {$lateCount}\n" .
                       "⏰ Avg Work Hours: " . round($avgWorkHours, 1) . "h/day\n";

            // Today's summary
            $todayPresent = Attendance::whereDate('date', Carbon::today())->count();
            $todayAbsent = $totalUsers - $todayPresent;
            $message .= "\n📅 Today:\n" .
                       "✅ Present: {$todayPresent}\n" .
                       "❌ Absent: {$todayAbsent}";

            return [
                'type' => 'statistics',
                'message' => $message,
                'data' => [
                    'total_users' => $totalUsers,
                    'total_attendances' => $totalAttendances,
                    'late_count' => $lateCount,
                    'avg_work_hours' => round($avgWorkHours, 1)
                ]
            ];
        } else {
            // Personal statistics
            return $this->getMyAttendance($query, $user);
        }
    }

    /**
     * Get work hours
     */
    private function getWorkHours(string $query, User $user): array
    {
        $period = $this->extractPeriod($query);
        
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$period['start'], $period['end']])
            ->get();

        $totalHours = $attendances->sum('work_hours') ?? 0;
        $avgHours = $attendances->avg('work_hours') ?? 0;
        $daysWorked = $attendances->count();

        $message = "🕐 Work Hours ({$period['label']}):\n\n" .
                   "📊 Total Hours: " . round($totalHours, 1) . "h\n" .
                   "📈 Average: " . round($avgHours, 1) . "h/day\n" .
                   "📅 Days Worked: {$daysWorked}\n";

        return [
            'type' => 'statistics',
            'message' => $message,
            'data' => [
                'total_hours' => round($totalHours, 1),
                'avg_hours' => round($avgHours, 1),
                'days_worked' => $daysWorked
            ]
        ];
    }

    /**
     * Get specific user attendance (admin/manager only)
     */
    private function getUserAttendance(string $query, User $currentUser): array
    {
        if (!$this->canViewAllAttendance($currentUser)) {
            return [
                'type' => 'text',
                'message' => "🔒 You don't have permission to view other employees' attendance."
            ];
        }

        // Try to extract user name from query
        $words = explode(' ', $query);
        $nameIndex = array_search('for', $words) ?: array_search('of', $words);
        
        if ($nameIndex === false || !isset($words[$nameIndex + 1])) {
            return [
                'type' => 'text',
                'message' => "Please specify a name. Example: 'Show attendance for John'"
            ];
        }

        $searchName = implode(' ', array_slice($words, $nameIndex + 1));
        $user = User::where('name', 'like', "%{$searchName}%")->first();

        if (!$user) {
            return [
                'type' => 'text',
                'message' => "❌ User '{$searchName}' not found. Please check the name and try again."
            ];
        }

        $period = $this->extractPeriod($query);
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$period['start'], $period['end']])
            ->orderBy('date', 'desc')
            ->get();

        $totalDays = $attendances->count();
        $lateDays = $attendances->where('is_late', true)->count();
        $totalHours = $attendances->sum('work_hours') ?? 0;

        $message = "📊 {$user->name}'s Attendance ({$period['label']}):\n\n" .
                   "✅ Days Present: {$totalDays}\n" .
                   "⚠️ Late Days: {$lateDays}\n" .
                   "🕐 Total Hours: " . round($totalHours, 1) . "h\n";

        return [
            'type' => 'attendance_list',
            'message' => $message,
            'data' => $attendances->take(10)->values()
        ];
    }

    /**
     * Get help information
     */
    private function getHelp(): array
    {
        $message = "🤖 Attendance Chatbot Help\n\n" .
                   "I can help you with:\n\n" .
                   "📋 Personal Queries:\n" .
                   "• 'Show my attendance this month'\n" .
                   "• 'Am I late today?'\n" .
                   "• 'My work hours this week'\n" .
                   "• 'My attendance statistics'\n\n" .
                   "👥 Manager/Admin Queries:\n" .
                   "• 'Who is absent today?'\n" .
                   "• 'Who is late today?'\n" .
                   "• 'Show attendance for [name]'\n" .
                   "• 'Overall statistics'\n\n" .
                   "⏰ Time Periods:\n" .
                   "Use: today, this week, this month, this year, last week, last month";

        return [
            'type' => 'help',
            'message' => $message
        ];
    }

    /**
     * Extract time period from query
     */
    private function extractPeriod(string $query): array
    {
        $now = Carbon::now();

        if (str_contains($query, 'today')) {
            return [
                'start' => Carbon::today(),
                'end' => Carbon::today(),
                'label' => 'Today'
            ];
        }

        if (str_contains($query, 'this week')) {
            return [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
                'label' => 'This Week'
            ];
        }

        if (str_contains($query, 'last week')) {
            return [
                'start' => $now->copy()->subWeek()->startOfWeek(),
                'end' => $now->copy()->subWeek()->endOfWeek(),
                'label' => 'Last Week'
            ];
        }

        if (str_contains($query, 'this month')) {
            return [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
                'label' => 'This Month'
            ];
        }

        if (str_contains($query, 'last month')) {
            return [
                'start' => $now->copy()->subMonth()->startOfMonth(),
                'end' => $now->copy()->subMonth()->endOfMonth(),
                'label' => 'Last Month'
            ];
        }

        if (str_contains($query, 'this year')) {
            return [
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfYear(),
                'label' => 'This Year'
            ];
        }

        // Default to this month
        return [
            'start' => $now->copy()->startOfMonth(),
            'end' => $now->copy()->endOfMonth(),
            'label' => 'This Month'
        ];
    }

    /**
     * Check if user can view all attendance records
     */
    private function canViewAllAttendance(User $user): bool
    {
        return $user->isSuperAdmin() || $user->role?->name === 'HR Manager';
    }
}
