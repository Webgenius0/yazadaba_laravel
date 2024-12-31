<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnroll;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceValueController extends Controller
{
    public function RevenueBreakdown(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        // Check if the user is a teacher
        if ($user->role !== 'teacher') {
            return Helper::jsonErrorResponse('Access denied. User is not a teacher.', 403);
        }

        // Get the user's courses
        $courses = Course::where('user_id', auth()->id())->pluck('id');

        // Determine if a specific year or month is selected from the request
        $selectedYear = $request->input('year');  // If the user selects a year
        $selectedMonth = $request->input('month');  // If the user selects a specific month

        // Base query to get sales data
        $salesQuery = CourseEnroll::whereIn('course_id', $courses)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, WEEK(created_at) as week, SUM(amount) as amount')
            ->groupBy('year', 'month', 'week');

        // Filter data by selected year if provided
        if ($selectedYear) {
            $salesQuery->whereYear('created_at', $selectedYear);
        }

        // Filter data by selected month if provided
        if ($selectedMonth) {
            $salesQuery->whereMonth('created_at', $selectedMonth);
        }

        // Get the sales data based on the filters
        $salesData = $salesQuery->get();

        // Initialize the months array
        $months = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];

        // Get the current year dynamically using Carbon
        $currentYear = Carbon::now()->year;

        // Prepare arrays for original and adjusted sales data
        $originalResult = [];
        $adjustedResult = [];
        $previousMonthAmount = 0;

        // Loop through all months and ensure every month is included in both arrays
        foreach ($months as $index => $monthName) {
            // Check for sales data for the current month
            $monthData = $salesData->firstWhere('month', $index + 1);

            // Get the amount for the current month (or 0 if no sales)
            $currentMonthAmount = $monthData ? $monthData->amount : 0;
            $currentWeek = $monthData ? $monthData->week : null;

            // Original result array (without changes)
            $originalResult[] = [
                'year' => $currentYear,
                'month' => $monthName,
                'week' => $currentWeek,  // Include week here
                'amount' => $currentMonthAmount,
            ];

            // Adjusted result array (apply the 25% reduction on adjusted revenue)
            $adjustedAmount = $currentMonthAmount;

            // If previous month's amount exists, apply the 25% increase check
            if ($previousMonthAmount > 0 && $currentMonthAmount >= $previousMonthAmount * 1.25) {
                $adjustedAmount = $previousMonthAmount * 1.25;
            }

            // Increase adjusted amount by an additional 25%
            $adjustedAmount *= 1.25;

            // Apply a 25% reduction to the adjusted amount
            $adjustedAmount *= 0.75;

            // Add the reduced adjusted amount to the adjusted result
            $adjustedResult[] = [
                'year' => $currentYear,
                'month' => $monthName,
                'week' => $currentWeek,
                'amount' => round($adjustedAmount, 2),
            ];

            $previousMonthAmount = $adjustedAmount / 0.75;
        }

        // Return both the original and adjusted data as separate collections
        // If no specific month is selected, return aggregated year data
        return response()->json([
            'status' => true,
            'message' => 'Resources Performance Metrics retrieved successfully',
            'code' => 200,
            'data' => [
                'totalSales' => $originalResult,
                'revenue' => $adjustedResult,
            ]
        ], 200);
    }

}
