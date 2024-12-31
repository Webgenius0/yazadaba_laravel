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

            // Check if the user has the 'teacher' role
            if ($user->role !== 'student') {
                return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
            }
            $CourseCategory = Category::all()->makeHidden(['created_at', 'updated_at', 'status']);
            if ($CourseCategory->isEmpty()) {
                return Helper::jsonErrorResponse('Course category does not exist.', 200, []);
            }
            //Continue Learning process in courses
            $learningCourses = CourseEnroll::where('user_id', $user->id)
                ->where('status', 'completed')
                ->distinct('course_id')
                ->with(['course' => function ($query) {
                    $query->select('id', 'name', 'category_id', 'cover_image', 'course_duration');
                }])
                ->get();

            $Courses = Course::withCount('reviews')
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

            if ($Courses->isEmpty()) {
                return Helper::jsonResponse(true, 'Categories retrieved successfully, but no courses available.', 200, [
                    'category' => $CourseCategory,
                    'courses' => [],
                    'learningCourses' => [],
                ]);
            }

            // Prepare the learningCourses data and calculate completion percentage
            $learningCoursesData = $learningCourses->map(function ($enrollment) use ($user) {
                // Calculate the total number of modules for the course
                $totalModules = $enrollment->course->courseModules->count();

                // Count the number of completed modules by the user
                $completedModules = $enrollment->course->isCompletes()
                    ->where('user_id', $user->id)
                    ->where('status', 'complete')
                    ->count();

                // Calculate the completion percentage
                $completionPercentage = $totalModules ? ($completedModules / $totalModules) * 100 : 0;

                // Use round() to round the completion percentage to the nearest whole number
                $completionPercentage = round($completionPercentage);

               // Calculate total duration of modules (without using DB::raw)
                $totalDurationInSeconds = $enrollment->course->courseModules->sum(function ($module) {
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
                    'id' => $enrollment->course->id,
                    'category' => $enrollment->course->category->name,
                    'name' => $enrollment->course->name,
                    'cover_image' => $enrollment->course->cover_image,
                    'course_duration' => $formattedDuration,
                    'completion_percentage' => $completionPercentage,
                ];
            });
            // Remove duplicate courses based on course_id
            $learningCoursesData = $learningCoursesData->unique('id');

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

}
