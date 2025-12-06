<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Get aggregated statistics for the Dashboard, optimized into two database queries.
     */
    public function getStats()
    {
        try {
            // 1. Core Count for Students (One Query)
            $totalStudents = Student::count();

            // 2. Incident Counts (Total and Detailed Statuses) in a single, efficient Query
            $incidentStats = Incident::select(
                DB::raw('COUNT(*) as totalReports'),
                DB::raw("SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pendingReports"),
                DB::raw("SUM(CASE WHEN status = 'Under Review' THEN 1 ELSE 0 END) AS underReviewReports"),
                DB::raw("SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolvedReports"),
                DB::raw("SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) AS closedReports")
            )->first(); 

            // Extract results, ensuring we cast raw DB results to integer
            $totalReports = (int)($incidentStats->totalReports ?? 0);
            $pendingReports = (int)($incidentStats->pendingReports ?? 0);
            $underReviewReports = (int)($incidentStats->underReviewReports ?? 0);
            $resolvedReports = (int)($incidentStats->resolvedReports ?? 0);
            $closedReports = (int)($incidentStats->closedReports ?? 0);

            // Return structured data
            return response()->json([
                'students' => $totalStudents,
                'reports' => $totalReports,
                'pending' => $pendingReports,
                'underReview' => $underReviewReports, 
                'resolved' => $resolvedReports, 
                'closed' => $closedReports, 
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Dashboard stats fetch failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve dashboard statistics.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the count of misconduct incidents grouped by month for the last 12 months.
     */
    public function getMonthlyMisconduct()
    {
        try {
            $startDate = Carbon::now()->subMonths(12)->startOfMonth();
            
            $monthlyReports = Incident::select(
                    DB::raw('COUNT(*) as count'),
                    DB::raw("DATE_FORMAT(date_of_incident, '%Y%m') as month_year_sort"), 
                    DB::raw("DATE_FORMAT(date_of_incident, '%b %Y') as month_label")
                )
                ->where('date_of_incident', '>=', $startDate)
                ->groupBy('month_year_sort', 'month_label')
                ->orderBy('month_year_sort', 'asc')
                ->get();
                
            return response()->json($monthlyReports, 200);

        } catch (\Exception $e) {
            \Log::error('Monthly misconduct fetch failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve monthly misconduct data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get the count of incidents grouped by their offense category.
     */
    public function getMisconductDistribution()
    {
        try {
            $distribution = Incident::select('offense_category', DB::raw('COUNT(*) as count'))
                ->groupBy('offense_category')
                ->get()
                ->pluck('count', 'offense_category')
                ->all();

            return response()->json($distribution, 200);

        } catch (\Exception $e) {
            \Log::error('Misconduct distribution fetch failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve misconduct distribution data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the count of incidents grouped by their specific offense (e.g., 'Cheating', 'Vandalism').
     */
    public function getSpecificMisconductDistribution()
    {
        try {
            // Modify the query to group by the 'specific_offense' column instead of 'offense_category'
            $specificDistribution = Incident::select('specific_offense', DB::raw('COUNT(*) as count'))
                ->groupBy('specific_offense') // Group by the specific offense description
                ->orderBy('count', 'desc') // Optional: Order by count, descending
                ->get()
                ->pluck('count', 'specific_offense') // Pluck the results into a key-value array
                ->all();

            return response()->json($specificDistribution, 200);

        } catch (\Exception $e) {
            \Log::error('Specific misconduct distribution fetch failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve specific misconduct distribution data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
 * Get the count of misconduct incidents grouped by program.
 */
public function getMisconductPerProgram()
{
    try {
        $programCounts = Incident::select('program', DB::raw('COUNT(*) as count'))
            ->groupBy('program')
            ->orderBy('count', 'desc') // Optional: highest to lowest
            ->get()
            ->pluck('count', 'program')
            ->all();

        return response()->json($programCounts, 200);

    } catch (\Exception $e) {
        \Log::error('Misconduct per program fetch failed: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to retrieve misconduct count per program.',
            'error' => $e->getMessage()
        ], 500);
    }
}

}