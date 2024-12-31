<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseEnroll;
use App\Models\Review;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        // Check if the user has the 'teacher' role
        if ($user->role !== 'teacher') {
            return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
        }

        // Fetch course categories
        $CourseCategory = Category::all()->makeHidden(['created_at', 'updated_at', 'status']);

        // Check if categories exist
        if ($CourseCategory->isEmpty()) {
            return Helper::jsonErrorResponse('Course category does not exist.', 200, []);
        }

        // Fetch courses belonging to the authenticated user (teacher)
        $Courses = Course::where('user_id', $user->id)
            ->get()
            ->map(function ($course) {
                // Count the number of reviews for the current course
                $reviewsCount = DB::table('reviews')
                    ->where('course_id', $course->id)
                    ->count(); // Counts reviews for the course based on course_id

                // Calculate the total duration of the course in seconds
                $totalDurationInSeconds = DB::table('course_modules')
                    ->where('course_id', $course->id)
                    ->sum(DB::raw('TIME_TO_SEC(module_video_duration)'));

                // Format the total duration
                if ($totalDurationInSeconds < 60) {
                    $formattedDuration = "{$totalDurationInSeconds} sec";
                } elseif ($totalDurationInSeconds < 3600) {
                    $formattedDuration = floor($totalDurationInSeconds / 60) . " min";
                } else {
                    $formattedDuration = floor($totalDurationInSeconds / 3600) . " hours";
                }

                // Add the calculated duration and reviews count to the course
                $course->course_duration = $formattedDuration;
                $course->reviews_count = $reviewsCount;  // Reviews count specific to this course
                $course->reviews_avg_rating = round($course->reviews_avg_rating ?? 0, 1);

                // Fetch user details (name and avatar)
                $course->user_id = DB::table('users')->where('id', $course->user_id)->value('name');
                $course->avatar = DB::table('users')->where('id', $course->user_id)->value('avatar');

                // Fetch course category name and grade level name
                $course->category_id = DB::table('categories')->where('id', $course->category_id)->value('name');
                $course->grade_level_id = DB::table('grade_levels')->where('id', $course->grade_level_id)->value('name');

                return $course;
            });

        // If no courses are found
        if ($Courses->isEmpty()) {
            return Helper::jsonResponse(true, 'Categories retrieved successfully, but no courses available.', 200, [
                'category' => $CourseCategory,
                'courses' => [],
            ]);
        }

        // Return the response with courses and categories
        return Helper::jsonResponse(true, 'Courses and Categories retrieved successfully.', 200, [
            'category' => $CourseCategory,
            'courses' => $Courses->makeHidden(['created_at', 'updated_at', 'status', 'deleted_at']),
        ]);
    }

    //category wised filter
    public function filterCategory(Request $request): \Illuminate\Http\JsonResponse
    {
        // Ensure the user is authenticated
        $user = Auth::user();
        if (!$user) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        // Check if the user is a teacher
        if ($user->role !== 'teacher') {
            return Helper::jsonErrorResponse('Access denied. User is not a teacher.', 403);
        }

        // Validate the category
        $category = Category::find($request->category_id);
        if (!$category) {
            return Helper::jsonErrorResponse('Category does not exist.', 404);
        }

        // Retrieve courses for the teacher and category
        $courses = Course::where('user_id', $user->id)
            ->where('category_id', $request->category_id)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get()
            ->map(function ($course) {
                // Calculate the total duration in seconds for the course
                $totalDurationInSeconds = DB::table('course_modules')
                    ->where('course_id', $course->id)
                    ->sum(DB::raw('TIME_TO_SEC(module_video_duration)'));

                if ($totalDurationInSeconds < 60) {
                    $formattedDuration = "{$totalDurationInSeconds} sec";
                } elseif ($totalDurationInSeconds < 3600) {
                    $formattedDuration = floor($totalDurationInSeconds / 60) . " min";
                } else {
                    $formattedDuration = floor($totalDurationInSeconds / 3600) . " hours";
                }
                $course->course_duration = $formattedDuration;
                $course->reviews_avg_rating = round($course->reviews_avg_rating ?? 0, 1);

                // Fetch related data like teacher's name, category, and grade level
                $course->user_id = DB::table('users')->where('id', $course->user_id)->value('name');
                $course->avatar = DB::table('users')->where('id', $course->user_id)->value('avatar');
                $course->category_id = DB::table('categories')->where('id', $course->category_id)->value('name');
                $course->grade_level_id = DB::table('grade_levels')->where('id', $course->grade_level_id)->value('name');
                return $course;
            });
        if ($courses->isEmpty()) {
            return Helper::jsonResponse(true, 'Courses retrieved successfully, but no courses available for this category.', 200, [
                'category' => $category,
                'courses' => [],
            ]);
        }

        // Return the response with courses
        return Helper::jsonResponse(true, 'Courses retrieved successfully.', 200, [
            'category' => $category,
            'courses' => $courses->makeHidden(['id', 'created_at', 'updated_at', 'status', 'deleted_at']),
        ]);
    }

    //search course name
    public function searchByCourse(Request $request): \Illuminate\Http\JsonResponse
    {
        // Ensure the user is authenticated
        $user = Auth::user();
        if (!$user) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        // Check if the user is a teacher
        if ($user->role !== 'teacher') {
            return Helper::jsonErrorResponse('Access denied. User is not a teacher.', 403);
        }

        // Validate the search query parameter
        $searchQuery = $request->input('name');
        if (empty($searchQuery)) {
            return Helper::jsonErrorResponse('Search query cannot be empty.', 400);
        }

        $courses = Course::where('user_id', $user->id)
            ->where('name', 'like', '%' . $searchQuery . '%')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->get()
            ->map(function ($course) {

                $totalDurationInSeconds = DB::table('course_modules')
                    ->where('course_id', $course->id)
                    ->sum(DB::raw('TIME_TO_SEC(module_video_duration)'));

                if ($totalDurationInSeconds < 60) {
                    $formattedDuration = "{$totalDurationInSeconds} sec";
                } elseif ($totalDurationInSeconds < 3600) {
                    $formattedDuration = floor($totalDurationInSeconds / 60) . " min";
                } else {
                    $formattedDuration = floor($totalDurationInSeconds / 3600) . " hours";
                }
                $course->course_duration = $formattedDuration;

                $course->reviews_avg_rating = round($course->reviews_avg_rating ?? 0, 1);
                $course->user_id = DB::table('users')->where('id', $course->user_id)->value('name');
                $course->avatar = DB::table('users')->where('id', $course->user_id)->value('avatar');
                $course->category_id = DB::table('categories')->where('id', $course->category_id)->value('name');
                $course->grade_level_id = DB::table('grade_levels')->where('id', $course->grade_level_id)->value('name');

                return $course;
            });
        if ($courses->isEmpty()) {
            return Helper::jsonResponse(true, 'No courses found matching the search criteria.', 200, [
                'courses' => [],
            ]);
        }
        return Helper::jsonResponse(true, 'Courses retrieved successfully.', 200, [
            'courses' => $courses->makeHidden(['id', 'created_at', 'updated_at', 'status', 'deleted_at']),
        ]);
    }

    public function sales(Request $request)
    {
        try {
            $user = Auth::user();

            // Ensure user is authenticated and is a teacher
            if (!$user || $user->role !== 'teacher') {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get total statistics for the authenticated teacher's courses
            $courses = Course::where('user_id', $user->id)->pluck('id');
            $totalCourses = $courses->count();
            $totalReviews = Review::where('user_id', $user->id)->count();
            $totalPrice = Course::where('user_id', $user->id)->sum('price');
            $totalEarning = CourseEnroll::whereIn('course_id', $courses)->sum('amount');
            $totalStudentCount = CourseEnroll::whereIn('course_id', $courses)->count();

            // Get the year and month from the request, defaulting to current year
            $year = $request->input('year', Carbon::now()->year);
            $month = $request->input('month', null);

            if ($month) {
                // Weekly sales data for a specific month
                $startOfMonth = Carbon::create($year, $month, 1);
                $weeksInMonth = ceil($startOfMonth->daysInMonth / 7);
                $salesReview = [];

                for ($week = 1; $week <= $weeksInMonth; $week++) {
                    $weekStart = $startOfMonth->copy()->addWeeks($week - 1)->startOfWeek();
                    $weekEnd = min($weekStart->copy()->endOfWeek(), $startOfMonth->copy()->endOfMonth());
                    $weekAmount = CourseEnroll::whereIn('course_id', $courses)
                        ->where('status', 'completed')
                        ->whereBetween('created_at', [$weekStart, $weekEnd])
                        ->sum('amount');

                    $salesReview[] = ['week' => $week, 'amount' => $weekAmount];
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Weekly sales data retrieved successfully.',
                    'data' => compact('totalCourses', 'totalReviews', 'totalPrice', 'totalEarning', 'totalStudentCount', 'salesReview')
                ], 200);
            }

            // Monthly sales data for a specific year
            $courseGraph = CourseEnroll::whereIn('course_id', $courses)
                ->where('status', 'completed')
                ->whereYear('created_at', $year)
                ->selectRaw('MONTH(created_at) as month, SUM(amount) as total_amount')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month')
                ->mapWithKeys(fn($item) => [$item->month => $item->total_amount]);

            $courseGraphData = collect(range(1, 12))->map(fn($month) => [
                'year' => $year,
                'month' => Carbon::create($year, $month)->format('M'),
                'amount' => $courseGraph[$month] ?? 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Monthly sales data retrieved successfully.',
                'data' => compact('totalCourses', 'totalReviews', 'totalPrice', 'totalEarning', 'totalStudentCount', 'courseGraphData')
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



}
