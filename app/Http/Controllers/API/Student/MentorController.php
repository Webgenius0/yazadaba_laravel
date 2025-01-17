<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnroll;
use App\Models\Review;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MentorController extends Controller
{
    public function index($user_id): \Illuminate\Http\JsonResponse
    {
        try {
            // Ensure the user is authenticated
            $userId = Auth::user();
            if (!$userId) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }
            if ($userId->role !== 'student') {
                return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
            }
            // Find the user by the provided user_id
            $user = User::find($user_id);
            // If user is not found
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                    'code' => 404
                ],404);
            }
            // Fetch total courses for this user
            $totalCourses = Course::where('user_id', $user->id)->where('status','active')->count();

            $totalCourses = Course::where('user_id', $user->id)->count();

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
            // Calculate average rating for this user's courses
            $totalReviews = (float)round(Review::join('courses', 'reviews.course_id', '=', 'courses.id')
                ->where('courses.user_id', $user->id)
                ->avg('reviews.rating') ?? 0.0, 1);

            // Initialize total students to 0 (as per your original logic)
            $totalStudents = 0;

            // Fetch courses for this user
            $courses = Course::with(['category', 'gradeLevel'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->get()
                ->map(function ($course) {
                    // Sum the module video durations for the course
                    $totalDurationInSeconds = DB::table('course_modules')
                        ->where('course_id', $course->id)
                        ->sum(DB::raw('TIME_TO_SEC(module_video_duration)'));

                    // Format course duration
                    if ($totalDurationInSeconds < 60) {
                        $formattedDuration = "{$totalDurationInSeconds} sec";
                    } elseif ($totalDurationInSeconds < 3600) {
                        $formattedDuration = floor($totalDurationInSeconds / 60) . " min";
                    } else {
                        $formattedDuration = floor($totalDurationInSeconds / 3600) . " hours";
                    }
                    $course->course_duration = $formattedDuration;

                    // Calculate ratings for the course
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

            // Hide unnecessary fields from the course
            $courses->makeHidden(['created_at', 'updated_at', 'deleted_at', 'description', 'status']);

            // Prepare the response data
            $data = [
                'total_courses' => $totalCourses,
                'total_reviews' => $totalReviews,
                'total_students' => $totalStudents,
                'user_details' => [
                    'id' => $user->id,
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
                            $timeSinceCreated = $rating->created_at->diffForHumans();
                            return [
                                'user_id' => $rating->user_id,
                                'user_name' => $rating->user->name,
                                'avatar' => $rating->user->avatar,
                                'review' => $rating->review,
                                'rating' => $rating->rating,
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

            // Return success response with the data
            return response()->json([
                'success' => true,
                'message' => 'Data fetched successfully.',
                'status_code' => 200,
                'data' => $data
            ]);

        } catch (Exception $e) {
            // Return error response if an exception occurs
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'status_code' => 500
            ]);
        }
    }
}
