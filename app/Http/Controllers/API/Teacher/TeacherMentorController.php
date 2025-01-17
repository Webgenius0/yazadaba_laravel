<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnroll;
use App\Models\Review;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TeacherMentorController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }

            if ($user->role !== 'teacher') {
                return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
            }

            $totalCourses = Course::where('user_id', $user->id)->where('status','active')->count();
            $courses = Course::where('user_id', $user->id)->where('status', 'active')->pluck('id');
            $totalStudents = CourseEnroll::whereIn('course_id', $courses)
                ->where('status', 'completed')
                ->count();

            $reviews = Review::whereIn('course_id', $courses)
                ->with(['user:id,name,avatar,created_at', 'course:id,name'])
                ->get();

            $totalReviews = $reviews->count();

            $averageRating = $totalReviews > 0
                ? round($reviews->avg('rating'), 1)
                : 0.0;

            $courses = Course::with(['category', 'gradeLevel'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->get()
                ->map(function ($course) {
                    // Sum the module video durations for the course
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

                    // Calculate the total ratings and average rating for the course
                    $course->total_ratings = $course->reviews()->count();
                    $course->average_rating = (float)round($course->reviews()->avg('rating') ?? 0.0, 1);

                    // Add category and grade level names
                    $course->category_name = $course->category->name ?? null;
                    $course->grade_level_name = $course->gradeLevel->name ?? null;

                    // Fetch the reviews and ratings for the course
                    $course->ratings = $course->reviews()
                        ->select('user_id', 'review', 'rating', 'created_at')
                        ->get();
                    return $course;
                });

            $courses->makeHidden(['created_at', 'updated_at', 'deleted_at', 'description', 'status']);

            $data = [
                'total_courses' => $totalCourses,
                'total_reviews' => $totalReviews,
                'total_students' => $totalStudents,
                'average_ratting' => $averageRating,
                'user_details' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $user->role,
                ],
                'courses' => $courses->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'cover_image' => $course->cover_image,
                        'category_name' => $course->category_name,
                        'grade_level_name' => $course->grade_level_name,
                        'total_ratings' => $course->total_ratings,
                        'average_rating' => $course->average_rating,
                        'course_price' => $course->price,
                        'total_course_duration' => $course->course_duration,
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
                    ];
                }),
                'reviews' => $reviews->map(function ($rating) {
                    $timeCreated = $rating->created_at ? $rating->created_at->diffForHumans() : '';
                    return [
                        'reviewer_id' => $rating->user->id,
                        'avatar' => $rating->user->avatar,
                        'name' => $rating->user->name,
                        'rating' => (float)$rating->rating,
                        'review' => $rating->review,
                        'created_at' => $timeCreated,
                    ];
                }),
            ];

            return Helper::jsonResponse(true, 'Data Fetch Successfully', 200, $data);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }
}
