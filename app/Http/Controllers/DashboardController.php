<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Incident;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get aggregated statistics for the Admin Dashboard, including detailed incident statuses.
     */
    public function getStats()
    {
        try {
            // 1. Core Counts
            $totalStudents = Student::count();
            $totalReports = Incident::count();
            
            // 2. Detailed Incident Status Counts
            // Assuming the 'status' column holds these exact strings:
            $pendingReports = Incident::where('status', 'Pending')->count();
            $underReviewReports = Incident::where('status', 'Under Review')->count();
            $resolvedReports = Incident::where('status', 'Resolved')->count();
            $closedReports = Incident::where('status', 'Closed')->count();

            // 3. Return the comprehensive structured data, matching Vue's expected camelCase keys
            return response()->json([
                'students' => $totalStudents,
                'reports' => $totalReports,
                'pending' => $pendingReports,
                'underReview' => $underReviewReports, // NEW
                'resolved' => $resolvedReports,       // NEW
                'closed' => $closedReports,           // NEW
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Dashboard stats fetch failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve dashboard statistics.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}