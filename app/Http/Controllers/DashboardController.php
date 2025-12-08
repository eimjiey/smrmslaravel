<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    
    public function getStats()
    {
        try {
            $totalStudents = Student::count();

            $incidentStats = Incident::select(
                DB::raw('COUNT(*) as totalReports'),
                DB::raw("SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pendingReports"),
                DB::raw("SUM(CASE WHEN status = 'Investigation' THEN 1 ELSE 0 END) AS underReviewReports"),
                DB::raw("SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) AS resolvedReports"),
                DB::raw("SUM(CASE WHEN status = 'Closed' THEN 1 ELSE 0 END) AS closedReports")
            )->first();

            return response()->json([
                'students'    => (int)($totalStudents ?? 0),
                'reports'     => (int)($incidentStats->totalReports ?? 0),
                'pending'     => (int)($incidentStats->pendingReports ?? 0),
                'underReview' => (int)($incidentStats->underReviewReports ?? 0),
                'resolved'    => (int)($incidentStats->resolvedReports ?? 0),
                'closed'      => (int)($incidentStats->closedReports ?? 0),
            ], 200);

        } catch (\Exception $e) {
            Log::error('Dashboard stats fetch failed: '.$e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve dashboard statistics.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


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
            Log::error('Monthly misconduct fetch failed: '.$e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve monthly misconduct data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function getOffenseTypeDistribution()
    {
        try {
            $distribution = Incident::select(
                    DB::raw("COALESCE(offense_categories.name, 'Unknown Category') as offense_category_name"),
                    DB::raw('COUNT(incidents.id) as count')
                )
                ->leftJoin('offense_categories', 'incidents.category_id', '=', 'offense_categories.id')
                ->groupBy('offense_category_name')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'offense_category_name')
                ->all();

            return response()->json($distribution, 200);
        } catch (\Exception $e) {
            \Log::error('Offense type distribution fetch failed: '.$e->getMessage());
            return response()->json(['message' => 'Failed to retrieve offense type distribution data.'], 500);
        }
    }

    public function getSpecificMisconductDistribution()
    {
        try {
            $specificDistribution = Incident::select(
                    DB::raw("COALESCE(offenses.name, 'Unknown Offense') as specific_offense_name"),
                    DB::raw('COUNT(incidents.id) as count')
                )
                ->leftJoin('offenses', 'incidents.specific_offense_id', '=', 'offenses.id')
                ->groupBy('specific_offense_name')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'specific_offense_name')
                ->all();

            return response()->json($specificDistribution, 200);
        } catch (\Exception $e) {
            Log::error('Specific distribution fetch failed: '.$e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve specific misconduct distribution data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function getMisconductPerProgram()
    {
        try {
            $programCounts = Incident::select(
                    DB::raw("COALESCE(programs.code, 'Unknown Program') as program_name"),
                    DB::raw('COUNT(incidents.id) as count')
                )
                // incidents.student_id (string) -> students.student_number (string)
                ->leftJoin('students', 'incidents.student_id', '=', 'students.student_number')
                // students.program_id -> programs.id
                ->leftJoin('programs', 'students.program_id', '=', 'programs.id')
                ->groupBy('program_name')
                ->orderBy('count', 'desc')
                ->get()
                ->pluck('count', 'program_name')
                ->all();

            return response()->json($programCounts, 200);

        } catch (\Exception $e) {
            Log::error('Misconduct per program fetch failed: '.$e->getMessage());
            return response()->json([
                'message' => 'Failed to retrieve misconduct count per program.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function getPredictiveMisconduct()
    {
        try {
            $historyStart = Carbon::createFromDate(2025, 8, 1)->startOfMonth();
            $historyEnd   = Carbon::createFromDate(2025, 12, 1)->endOfMonth();

            $targetMonths = [
                Carbon::createFromDate(2026, 1, 1),
                Carbon::createFromDate(2026, 2, 1),
                Carbon::createFromDate(2026, 3, 1),
                Carbon::createFromDate(2026, 4, 1),
                Carbon::createFromDate(2026, 5, 1),
            ];

            $allMonths     = [];
            $currentMonth  = $historyStart->copy();
            $forecastEnd   = end($targetMonths)->copy();

            while ($currentMonth <= $forecastEnd) {
                $isPrediction = $currentMonth->year === 2026;
                $allMonths[$currentMonth->format('Y-m')] = [
                    'month_label'   => $currentMonth->format('M Y'),
                    'count'         => 0,
                    'is_prediction' => $isPrediction,
                ];
                $currentMonth->addMonth();
            }

            $historicalData = Incident::select(
                    DB::raw('COUNT(*) as count'),
                    DB::raw("DATE_FORMAT(date_of_incident, '%Y-%m') as month_key")
                )
                ->whereBetween('date_of_incident', [$historyStart, $historyEnd])
                ->groupBy('month_key')
                ->orderBy('month_key', 'asc')
                ->get();

            $actualData = $allMonths;
            foreach ($historicalData as $record) {
                if (isset($actualData[$record->month_key])) {
                    $actualData[$record->month_key]['count']         = (int) $record->count;
                    $actualData[$record->month_key]['is_prediction'] = false;
                }
            }

            $p1_key = Carbon::createFromDate(2025, 11, 1)->format('Y-m');
            $p2_key = Carbon::createFromDate(2025, 12, 1)->format('Y-m');

            if (!isset($actualData[$p1_key]) || !isset($actualData[$p2_key])) {
                return response()->json([
                    'message' => 'Baseline data (Nov/Dec 2025) is missing to perform prediction.',
                ], 200);
            }

            $p1_count = $actualData[$p1_key]['count'];
            $p2_count = $actualData[$p2_key]['count'];

            if ($p1_count > 0 && $p2_count > 0) {
                $growthRate = ($p2_count - $p1_count) / $p1_count;
            } elseif ($p1_count == 0 && $p2_count > 0) {
                $growthRate = $p2_count;
            } else {
                $growthRate = 0;
            }

            $previousPrediction = $p2_count;

            foreach ($targetMonths as $date) {
                $month_key = $date->format('Y-m');

                $nextPrediction = round($previousPrediction * (1 + $growthRate));
                $nextPrediction = max(0, $nextPrediction);

                $actualData[$month_key] = [
                    'month_label'   => $date->format('M Y'),
                    'count'         => $nextPrediction,
                    'is_prediction' => true,
                ];

                $previousPrediction = $nextPrediction;
            }

            $finalForecast = array_values($actualData);

            return response()->json([
                'forecast_data' => $finalForecast,
                'metadata'      => [
                    'baseline_period_1'        => Carbon::createFromFormat('Y-m', $p1_key)->format('M Y'),
                    'baseline_period_1_count'  => $p1_count,
                    'baseline_period_2'        => Carbon::createFromFormat('Y-m', $p2_key)->format('M Y'),
                    'baseline_period_2_count'  => $p2_count,
                    'calculated_growth_rate'   => round($growthRate * 100, 2) . '%',
                    'prediction_method'        => 'Geometric Growth Rate (based on Nov-Dec 2025 data)',
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Predictive analysis failed: '.$e->getMessage());
            return response()->json([
                'message' => 'An internal error occurred during prediction.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
