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

        // Ensure the user is authenticated and is a teacher
        if (!$user || $user->role !== 'teacher') {
            return Helper::jsonErrorResponse('Access denied. User not authenticated or not a teacher.', 403);
        }

        // Get courses of the user
        $courses = Course::where('user_id', auth()->id())->pluck('id');

        // Get selected year and month from the request
        $selectedYear = $request->input('year', Carbon::now()->year);
        $selectedMonth = $request->input('month', null);

        // Validate future year or month
        if ($selectedYear > Carbon::now()->year || ($selectedYear == Carbon::now()->year && $selectedMonth > Carbon::now()->month)) {
            return Helper::jsonErrorResponse('Selected year or month is in the future.', 400);
        }

        // Build base query for sales data
        $salesQuery = CourseEnroll::whereIn('course_id', $courses)
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, WEEK(created_at) as week, SUM(amount) as amount')
            ->groupBy('year', 'month', 'week');

        // Apply year and month filters
        $salesQuery->whereYear('created_at', $selectedYear);
        if ($selectedMonth) $salesQuery->whereMonth('created_at', $selectedMonth);

        // Get the sales data
        $salesData = $salesQuery->get();

        // Prepare the result
        $result = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // If month is selected, get weekly data; otherwise, yearly data
        if ($selectedMonth !== null) {
            // Get weekly breakdown
            $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
            $endDate = $startDate->copy()->endOfMonth();

            for ($weekNumber = 1; $startDate->lte($endDate); $weekNumber++) {
                $weekStart = $startDate->copy()->startOfWeek();
                $weekEnd = $startDate->copy()->endOfWeek();
                $weekData = $salesData->firstWhere(fn($sale) => Carbon::parse($sale->created_at)->between($weekStart, $weekEnd));

                $amount = $weekData ? $weekData->amount : 0;
                $result['totalSales'][] = ['year' => $selectedYear, 'week' => $weekNumber, 'amount' => $amount];
                $adjustedAmount = round($amount * 1.25 * 0.75, 2);
                $result['revenue'][] = ['year' => $selectedYear, 'week' => $weekNumber, 'amount' => $adjustedAmount];

                $startDate->addWeek();
            }
        } else {
            // Get monthly breakdown
            foreach ($months as $index => $monthName) {
                $monthData = $salesData->firstWhere('month', $index + 1);
                $amount = $monthData ? $monthData->amount : 0;
                $result['totalSales'][] = ['year' => $selectedYear, 'month' => $monthName, 'amount' => $amount];
                $adjustedAmount = round($amount * 1.25 * 0.75, 2);
                $result['revenue'][] = ['year' => $selectedYear, 'month' => $monthName, 'amount' => $adjustedAmount];
            }
        }

        return response()->json([
            'status' => true,
            'message' => $selectedMonth !== null ? 'Monthly Revenue Breakdown retrieved successfully' : 'Yearly Revenue Breakdown retrieved successfully',
            'code' => 200,
            'data' => $result
        ], 200);
    }
}
