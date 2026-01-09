<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceExport;
use App\Exports\UserAttendanceExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class TestExportController extends Controller
{
    /**
     * Test basic attendance export
     */
    public function testBasicExport()
    {
        try {
            return Excel::download(
                new AttendanceExport(), 
                'test-attendance-' . date('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Test user-specific export
     */
    public function testUserExport($userId = 1)
    {
        try {
            return Excel::download(
                new UserAttendanceExport(
                    userId: $userId,
                    startDate: Carbon::now()->startOfMonth(),
                    endDate: Carbon::now()->endOfMonth()
                ),
                'test-user-attendance-' . date('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Test export info (doesn't download, just shows data)
     */
    public function testExportInfo()
    {
        try {
            $export = new AttendanceExport();
            $collection = $export->collection();
            
            return response()->json([
                'success' => true,
                'records_count' => $collection->count(),
                'sample_record' => $collection->first(),
                'has_users' => $collection->first() ? ($collection->first()->user ? true : false) : false,
                'message' => 'Export data loaded successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
