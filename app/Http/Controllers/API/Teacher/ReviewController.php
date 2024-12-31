<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Review;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            // Retrieve the authenticated user
            $user = auth()->user();

            // Check if the user is authenticated
            if (!$user) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }

            // Fetch all courses for the authenticated user
            $courses = Course::where('user_id', $user->id)->get();

            if ($courses->isEmpty()) {
                return Helper::jsonErrorResponse('No courses found for this teacher.', 404);
            }

            // Fetch all reviews written by the authenticated user
            $reviews = Review::where('user_id', $user->id)
                ->select('user_id', 'course_id', 'review', 'rating', 'created_at')
                ->get();

            $totalReviewsCount = $reviews->count();

            if ($totalReviewsCount === 0) {
                return Helper::jsonErrorResponse('No reviews found for this user.', 404);
            }

            // Format the `created_at` for each review to "X minutes ago"
            $reviews->each(function ($review) {
                // Ensure `created_at` is a Carbon instance and format it
                $review->created_at = Carbon::parse($review->created_at)->diffForHumans();
            });

            // Initialize rating counts for 1 to 5
            $ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

            // Loop through the reviews and count the ratings
            foreach ($reviews as $review) {
                if (isset($ratingCounts[$review->rating])) {
                    $ratingCounts[$review->rating]++;
                }
            }

            // Calculate the percentage for each rating and round to the nearest whole number
            $ratingPercentages = [];
            foreach ($ratingCounts as $rating => $count) {
                $ratingPercentages[$rating] = $totalReviewsCount > 0 ? round(($count / $totalReviewsCount) * 100) : 0;
            }

            // Prepare the final response data
            $data = [
                'total_reviews_count' => $totalReviewsCount,
                'rating_percentages' => $ratingPercentages,
                'reviews' => $reviews->map(function ($review) {
                    $timeSinceCreated = $review->created_at ? Carbon::parse($review->created_at)->diffForHumans() : 'N/A';

                    // Get the course details for the review
                    $course = Course::find($review->course_id);

                    return [
                        'course_id' => $review->course_id,
                        'user_id' => $course->user->name,
                        'course_name' => $course ? $course->name : 'Course not found',
                        'review' => $review->review,
                        'rating' => $review->rating,
                        'created_at' => $timeSinceCreated,
                    ];
                }),
            ];

            return Helper::jsonResponse(true, 'Review Data fetched successfully', 200, $data);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }

    public function submitReview(Request $request, $courseId): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'review' => 'required|string|max:255',
            'rating' => 'required|integer|between:0,5',
        ]);

        try {
            $review = new Review();
            $review->user_id = Auth::id();
            $review->course_id = $courseId;
            $review->review = $validated['review'];
            $review->rating = $validated['rating'];
            $review->save();

          return Helper::jsonResponse(true, 'Review submitted successfully', 200,$review);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }



}
