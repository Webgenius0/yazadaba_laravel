<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class CurriculumController extends Controller
{
    public function details(Request $request, $curriculum): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }
            if ($user->role !== 'teacher') {
                return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
            }

            $course = Course::with(['courseModules', 'category', 'gradeLevel'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->find($curriculum);

            if (!$course) {
                return Helper::jsonErrorResponse('Course not found.', 404);
            }

            // Format each module's video duration dynamically
            $course->courseModules->each(function ($module) {
                // Check if the duration is in the format of HH:MM:SS or MM:SS, or just seconds.
                if (strpos($module->module_video_duration, ':') !== false) {
                    // It's in HH:MM:SS or MM:SS format
                    $timeParts = explode(':', $module->module_video_duration);
                    $totalSeconds = 0;

                    if (count($timeParts) === 3) {
                        // HH:MM:SS format
                        $totalSeconds = (int)$timeParts[0] * 3600 + (int)$timeParts[1] * 60 + (int)$timeParts[2];
                    } else {
                        // MM:SS format
                        $totalSeconds = (int)$timeParts[0] * 60 + (int)$timeParts[1];
                    }
                } else {
                    // It's just seconds (no colon)
                    $totalSeconds = (int)$module->module_video_duration;
                }

                // Format the duration
                if ($totalSeconds < 60) {
                    $module->module_video_duration = "{$totalSeconds} sec";
                } elseif ($totalSeconds < 3600) {
                    $module->module_video_duration = floor($totalSeconds / 60) . " min";
                } else {
                    $module->module_video_duration = floor($totalSeconds / 3600) . " hours " . floor(($totalSeconds % 3600) / 60) . " min";
                }
            });

            // Calculate total course duration
            $totalDurationInSeconds = DB::table('course_modules')
                ->where('course_id', $course->id)
                ->sum(DB::raw('TIME_TO_SEC(module_video_duration)'));

            // Format total course duration
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
                    'id'=>$user->id,
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
                        $user = $rating->user;
                        $timeSinceCreated = $rating->created_at->diffForHumans();
                        return [
                            'user_id' => $rating->user_id,
                            'user_name' => $user->name ?? 'User not found',
                            'avatar' => $user->avatar ?? null,
                            'review' => $rating->review,
                            'rating' => (float) number_format($rating->rating, 1, '.', ''),
                            'created_at' => $timeSinceCreated,
                        ];
                    }),
                ],
            ];

            return Helper::jsonResponse(true, 'Course Curriculum retrieved successfully.', 200, $courseData);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }
}
