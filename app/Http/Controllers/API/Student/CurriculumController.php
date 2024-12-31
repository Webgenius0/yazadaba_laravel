<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CurriculumController extends Controller
{
    public function details(Request $request, $curriculum): \Illuminate\Http\JsonResponse
    {
        try {

            $user = Auth::user();

            if (!$user) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }
            if ($user->role !== 'student') {
                return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
            }

            $course = Course::with(['courseModules', 'category', 'gradeLevel'])->where('status','inactive')->find
            ($curriculum);
            if (!$course) {
                return Helper::jsonErrorResponse('Course not found.', 404);
            }
            // Format each module's video duration dynamically
            $course->courseModules->each(function ($module) {
                // Convert video duration to seconds
                $totalSeconds = array_sum(array_map(static fn($time) => (int)$time, explode(':', $module->module_video_duration)));

                // Format the duration
                if ($totalSeconds < 60) {
                    $module->module_video_duration = "{$totalSeconds} sec";
                } elseif ($totalSeconds < 3600) {
                    $module->module_video_duration = floor($totalSeconds / 60) . " min";
                } else {
                    $module->module_video_duration = floor($totalSeconds / 3600) . " hours " . floor(($totalSeconds % 3600) / 60) . " min";
                }
            });

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
            // Add total ratings and average rating
            $course->total_ratings = $course->reviews()->count();
            $course->average_rating = (float)round($course->reviews()->avg('rating') ?? 0.0, 1);
            $course->ratings = $course->reviews()->select('user_id', 'review', 'rating', 'created_at')->get();

            $course->total_user_review = $course->reviews()->count();

            $course->makeHidden(['created_at', 'updated_at', 'deleted_at', 'status']);
            $course->courseModules->makeHidden(['created_at', 'updated_at']);

            $categoryName = $course->category->name ?? null;
            $gradeLevelName = $course->gradeLevel->name ?? null;

            $courseData = [
                'user_details' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                ],
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                    'category' => $categoryName,
                    'grade_level' => $gradeLevelName,
                    'price' => $course->price,
                    'total_ratings' => $course->total_ratings,
                    'average_rating' => $course->average_rating,
                    'total_user_review' => $course->total_user_review,
                    'total_course_duration' => $course->course_duration,
                    'course_modules' => $course->courseModules,
                    'ratings' => $course->ratings->map(function ($rating) {
                        $timeSinceCreated = $rating->created_at->diffForHumans();
                        return [
                            'user_id' => $rating->user_id,
                            'review' => $rating->review,
                            'rating' => $rating->rating,
                            'created_at' => $timeSinceCreated,
                        ];
                    }),
                ],
            ];
            return Helper::jsonResponse(true, 'Course Curriculum retrieved successfully.', 200, $courseData);
        }catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }
}
