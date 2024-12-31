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
        $CourseCategory = Category::all()->makeHidden(['created_at', 'updated_at', 'status']);
        if ($CourseCategory->isEmpty()) {
            return Helper::jsonErrorResponse('Course category does not exist.', 200, []);
        }
        $Courses = Course::where('user_id', $user->id)
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
                // Update the course's course_duration field in the database
                $course->course_duration = $formattedDuration;
                $course->reviews_avg_rating = round($course->reviews_avg_rating ?? 0, 1);
                $course->user_id = DB::table('users')->where('id', $course->user_id)->value('name');
                $course->category_id = DB::table('categories')->where('id', $course->category_id)->value('name');
                $course->grade_level_id = DB::table('grade_levels')->where('id', $course->grade_level_id)->value('name');

                return $course;
            });
        if ($Courses->isEmpty()) {
            return Helper::jsonResponse(true, 'Categories retrieved successfully, but no courses available.', 200, [
                'category' => $CourseCategory,
                'courses' => [],
            ]);
        }
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

    public function sales(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }

            // Check if the user is a teacher
            if ($user->role !== 'teacher') {
                return Helper::jsonErrorResponse('Access denied. User is not a teacher.', 403);
            }

            $totalCourses = Course::where('user_id', auth()->id())->count();
            $totalReviews = Review::where('user_id', auth()->id())->count();
            $courses = Course::where('user_id', auth()->id())->pluck('id');
            $totalPrice = Course::where('user_id', auth()->id())->sum('price');
            $totalEarning = CourseEnroll::whereIn('course_id', $courses)->sum('amount');

            // Get the total student count for the selected courses
            $totalStudentCount = CourseEnroll::whereIn('course_id', $courses)->count();

            // Define the months you're interested in (Jan, Mar, May, Jul, Sep, Nov, Dec)
            $months = [1,2, 3,4, 5,6, 7,8, 9,10, 11, 12];

            // Get the current year and month
            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;

            // Fetch the total amounts for each of the specified months
            $courseGraph = CourseEnroll::whereIn('course_id', $courses)->where('status','completed')
                ->whereYear('created_at', $currentYear)
                ->whereIn(DB::raw('MONTH(created_at)'), $months)
                ->selectRaw('MONTH(created_at) as month, SUM(amount) as total_amount')
                ->groupBy('month')
                ->orderBy('month')
                ->get()
                ->keyBy('month')
                ->mapWithKeys(function ($item) {
                    return [$item->month => $item->total_amount];
                });

            // Prepare the final result, including the current year and month
            $allMonths = [1,2, 3,4, 5,6, 7,8, 9,10, 11, 12];
            $courseGraphData = array_map(static function ($month) use ($courseGraph, $currentYear) {
                return [
                    'year' => $currentYear,
                    'month' => Carbon::create()->month($month)->format('M'),
                    'amount' => $courseGraph[$month] ?? 0,
                ];
            }, $allMonths);

            // Return all data in the response
            return Helper::jsonResponse(true, 'Courses retrieved successfully.', 200, [
                'total_courses' => $totalCourses,
                'total_reviews' => $totalReviews,
                'total_price' => $totalPrice,
                'total_earning' => $totalEarning,
                'total_student_count' => $totalStudentCount,
                'sales_review' => $courseGraphData,
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

}
