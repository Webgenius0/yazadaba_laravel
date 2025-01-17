<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnroll;
use App\Models\Review;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TusPhp\Response;

class ResourceValueController extends Controller
{
    public function index(){
        try {
            $user = Auth::user();

            // Ensure user is authenticated and is a teacher
            if (!$user || $user->role !== 'teacher') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            // Get total statistics for the authenticated teacher's courses
            $courses = Course::where('user_id', $user->id)->where('status','active')->pluck('id');
            $totalResourceValue = Course::where('user_id', $user->id)->where('status','active')->sum('price');
            $averageResourceValue = Course::where('user_id', $user->id)
                ->where('status', 'active')
                ->count()
                ? (int) (Course::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->sum('price') / Course::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->count())
                : 0;

            $totalResourceSold = CourseEnroll::whereIn('course_id', $courses)->where('status','completed')->count();
            $totalResourceSoldValue = CourseEnroll::whereIn('course_id', $courses)->where('status','completed')->sum('amount');
            $totalStudentEnroll = CourseEnroll::whereIn('course_id', $courses)->count();
            $averageEnrollPerResources = count($courses) > 0
                ? CourseEnroll::whereIn('course_id', $courses)->count() / count($courses)
                : 0;
            $newEnrollResources = CourseEnroll::whereIn('course_id', $courses)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->count();
            $topEnrollCourse = CourseEnroll::select('course_id', DB::raw('count(*) as enrollments'))
                ->groupBy('course_id')
                ->orderByDesc('enrollments')
                ->first();

            $topEnrollAmount = $topEnrollCourse
                ? $topEnrollCourse->enrollments * Course::find($topEnrollCourse->course_id)->price
                : 0;
            $data=[
                'totalResourceValue' => $totalResourceValue,
                'averageResourceValue' => $averageResourceValue,
                'totalResourceSold' => $totalResourceSold,
                'totalResourceSoldValue' => $totalResourceSoldValue,
                'totalStudentEnroll' => $totalStudentEnroll,
                'averageEnrollPerResources' => $averageEnrollPerResources,
                'newEnrollResources' => $newEnrollResources,
                'topEnrollAmount' => $topEnrollAmount,
                'topEnrollAmountValue' => $topEnrollAmount,
            ];
            return Helper::jsonResponse(true ,'Resource Value Fetch successfully',200,$data);
        }catch (Exception $e){
            Log::error($e->getMessage());
            return Helper::jsonResponse(false,' Error',500);
        }
    }
    public function RevenueBreakdown(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Ensure the user is authenticated and is a teacher
        if (!$user || $user->role !== 'teacher') {
            return Helper::jsonErrorResponse('Access denied. User not authenticated or not a teacher.', 403);
        }

        // Get courses of the user
        $courses = Course::where('user_id', auth()->id())->where('status','active')->pluck('id');

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
    public function EnrollmentCompletionBreakdown(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Ensure the user is authenticated and is a teacher
        if (!$user || $user->role !== 'teacher') {
            return Helper::jsonErrorResponse('Access denied. User not authenticated or not a teacher.', 403);
        }

        // Get courses of the user
        $courses = Course::where('user_id', auth()->id())->where('status','active')->pluck('id');

        // Get selected year and month from the request
        $selectedYear = $request->input('year', Carbon::now()->year);
        $selectedMonth = $request->input('month', null);

        // Validate future year or month
        if ($selectedYear > Carbon::now()->year || ($selectedYear == Carbon::now()->year && $selectedMonth > Carbon::now()->month)) {
            return Helper::jsonErrorResponse('Selected year or month is in the future.', 400);
        }

        // Prepare the result for both completions and enrollments
        $result = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // If month is selected, get weekly data; otherwise, yearly data
        if ($selectedMonth !== null) {
            // Get weekly breakdown for completions
            $startDate = Carbon::createFromDate($selectedYear, $selectedMonth, 1);
            $endDate = $startDate->copy()->endOfMonth();

            for ($weekNumber = 1; $startDate->lte($endDate); $weekNumber++) {
                $weekStart = $startDate->copy()->startOfWeek();
                $weekEnd = $startDate->copy()->endOfWeek();

                // Get completions for this week from i_s_completes table
                $completedCount = DB::table('i_s_completes')
                    ->whereIn('course_id', $courses)
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->where('status', 'complete')
                    ->count();

                // Get total amount of enrollments for this week from course_enrolls table
                $enrolledAmount = DB::table('course_enrolls')
                    ->whereIn('course_id', $courses)
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->sum('amount');

                // Store data for total completions and enrollments (with amount) for the week
                $result['totalCompletions'][] = ['year' => $selectedYear, 'week' => $weekNumber, 'amount' => $completedCount];
                $result['totalEnrollments'][] = ['year' => $selectedYear, 'week' => $weekNumber, 'amount' => $enrolledAmount];

                // Move to the next week
                $startDate->addWeek();
            }
        } else {
            // Get monthly breakdown for completions
            foreach ($months as $index => $monthName) {
                $monthStart = Carbon::createFromDate($selectedYear, $index + 1, 1);
                $monthEnd = $monthStart->copy()->endOfMonth();

                // Get completions for this month from i_s_completes table
                $completedCount = DB::table('i_s_completes')
                    ->whereIn('course_id', $courses)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->where('status', 'complete')
                    ->count();

                // Get total amount of enrollments for this month from course_enrolls table
                $enrolledAmount = DB::table('course_enrolls')
                    ->whereIn('course_id', $courses)
                    ->whereBetween('created_at', [$monthStart, $monthEnd])
                    ->sum('amount');

                // Store data for total completions and enrollments (with amount) for the month
                $result['totalCompletions'][] = ['year' => $selectedYear, 'month' => $monthName, 'amount' => $completedCount];
                $result['totalEnrollments'][] = ['year' => $selectedYear, 'month' => $monthName, 'amount' => $enrolledAmount];
            }
        }

        return response()->json([
            'status' => true,
            'message' => $selectedMonth !== null ? 'Monthly Enrollment and Completion Breakdown retrieved successfully' : 'Yearly Enrollment and Completion Breakdown retrieved successfully',
            'code' => 200,
            'data' => $result
        ], 200);
    }

}
