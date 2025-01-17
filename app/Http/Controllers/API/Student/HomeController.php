<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseEnroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\NoReturn;

class HomeController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if the user is authenticated
            if (!$user) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }

            // Check if the user has the 'student' role
            if ($user->role !== 'student') {
                return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
            }

            $CourseCategory = Category::all()->makeHidden(['created_at', 'updated_at', 'status']);
            if ($CourseCategory->isEmpty()) {
                return Helper::jsonErrorResponse('Course category does not exist.', 200, []);
            }

            // Continue Learning process in courses
            $learningCourses = CourseEnroll::where('user_id', $user->id)
                ->where('status', 'completed')
                ->distinct('course_id')
                ->with(['course' => function ($query) {
                    $query->select('id', 'name', 'category_id', 'cover_image', 'course_duration', 'is_enroll');
                }])
                ->get();

            $Courses = Course::withCount('reviews')->with('user')->where('status','active')
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
                    $course->category_id = DB::table('categories')->where('id', $course->category_id)->value('name');
                    $course->grade_level_id = DB::table('grade_levels')->where('id', $course->grade_level_id)->value('name');

                    // Check if the user is enrolled in the course and update the 'is_enroll' value
                    $enrollment = CourseEnroll::where('user_id', Auth::id())
                        ->where('course_id', $course->id)
                        ->first();

                    if ($enrollment) {
                        // If the user is enrolled, update 'is_enroll' to true
                        $course->is_enroll = 1;
                    } else {
                        // If not enrolled, set 'is_enroll' to false
                        $course->is_enroll = 0;
                    }

                    return $course;
                });

            if ($Courses->isEmpty()) {
                return Helper::jsonResponse(true, 'Categories retrieved successfully, but no courses available.', 200, [
                    'category' => $CourseCategory,
                    'courses' => [],
                    'learningCourses' => [],
                ]);
            }

            // Prepare the learningCourses data and calculate completion percentage
            $learningCoursesData = $learningCourses->map(function ($enrollment) use ($user) {
                $course = $enrollment->course;
                if (!$course) {
                    return [
                        'course_id' => 0,
                        'category' => '',
                        'name' => '',
                        'cover_image' => '',
                        'course_duration' => '',
                        'completion_percentage' => 0,
                    ];
                }

                // Calculate the total number of modules for the course
                $totalModules = $course->courseModules->count();

                // Count the number of completed modules by the user
                $completedModules = $course->isCompletes()
                    ->where('user_id', $user->id)
                    ->where('status', 'complete')
                    ->count();

                // Calculate the completion percentage
                $completionPercentage = $totalModules ? ($completedModules / $totalModules) * 100 : 0;

                // Round the completion percentage to the nearest whole number
                $completionPercentage = round($completionPercentage);

                // Calculate total duration of modules (without using DB::raw)
                $totalDurationInSeconds = $course->courseModules->sum(function ($module) {
                    // Convert module duration to seconds (assuming module_video_duration is in the correct format)
                    $durationParts = explode(':', $module->module_video_duration);
                    $hours = (int)($durationParts[0] ?? 0);
                    $minutes = (int)($durationParts[1] ?? 0);
                    $seconds = (int)($durationParts[2] ?? 0);

                    return ($hours * 3600) + ($minutes * 60) + $seconds;
                });

                // Format the duration (seconds, minutes, hours)
                if ($totalDurationInSeconds < 60) {
                    $formattedDuration = "{$totalDurationInSeconds} sec";
                } elseif ($totalDurationInSeconds < 3600) {
                    $formattedDuration = floor($totalDurationInSeconds / 60) . " min";
                } else {
                    $formattedDuration = floor($totalDurationInSeconds / 3600) . " hours";
                }

                return [
                    'course_id' => $course->id ?? 0,
                    'category' => $course->category->name ?? '',
                    'name' => $course->name ?? '',
                    'cover_image' => $course->cover_image ?? '',
                    'course_duration' => $formattedDuration,
                    'completion_percentage' => $completionPercentage,
                ];
            });

            // Remove duplicate courses based on course_id
            $learningCoursesData = $learningCoursesData->unique('course_id');

            return Helper::jsonResponse(true, 'Courses and Categories retrieved successfully.', 200, [
                'category' => $CourseCategory,
                'courses' => $Courses->makeHidden(['created_at', 'updated_at', 'status', 'deleted_at']),
                'learningCourses' => $learningCoursesData,
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonResponse(false, 'Something went wrong.', 500);
        }
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
        if ($user->role !== 'student') {
            return Helper::jsonErrorResponse('Access denied. User is not a student.', 403);
        }

        // Validate the category
        $category = Category::find($request->category_id);
        if (!$category) {
            return Helper::jsonErrorResponse('Category does not exist.', 404);
        }
        // Retrieve courses for the teacher and category
        $courses = Course::where('category_id', $request->category_id)->where('status','active')->with('user')
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

                $course->category_id = DB::table('categories')->where('id', $course->category_id)->value('name');
                $course->grade_level_id = DB::table('grade_levels')->where('id', $course->grade_level_id)->value('name');
                // Check if the user is enrolled in the course and update the 'is_enroll' value
                $enrollment = CourseEnroll::where('user_id', Auth::id())
                    ->where('course_id', $course->id)
                    ->first();

                if ($enrollment) {
                    // If the user is enrolled, update 'is_enroll' to true
                    $course->is_enroll = 1;
                } else {
                    // If not enrolled, set 'is_enroll' to false
                    $course->is_enroll = 0;
                }
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
        if ($user->role !== 'student') {
            return Helper::jsonErrorResponse('Access denied. User is not a student.', 403);
        }

        // Validate the search query parameter
        $searchQuery = $request->input('name');
        if (empty($searchQuery)) {
            return Helper::jsonErrorResponse('Search query cannot be empty.', 400);
        }

        $courses = Course::where('name', 'like', '%' . $searchQuery . '%')->where('status','active')
            ->withCount('reviews')->with('user')
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
                $course->category_id = DB::table('categories')->where('id', $course->category_id)->value('name');
                $course->grade_level_id = DB::table('grade_levels')->where('id', $course->grade_level_id)->value('name');
                // Check if the user is enrolled in the course and update the 'is_enroll' value
                $enrollment = CourseEnroll::where('user_id', Auth::id())
                    ->where('course_id', $course->id)
                    ->first();

                if ($enrollment) {
                    // If the user is enrolled, update 'is_enroll' to true
                    $course->is_enroll = 1;
                } else {
                    // If not enrolled, set 'is_enroll' to false
                    $course->is_enroll = 0;
                }
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
}
