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
            $user = auth()->user();

            if (!$user) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }
            $getUserCourse = Course::where('user_id', auth()->id())->where('status','active')->pluck('id');

            $reviews = Review::whereIn('course_id', $getUserCourse)
                ->with(['user:id,name,avatar', 'course:id,name'])
                ->get();

            if ($reviews->isEmpty()) {
                return Helper::jsonErrorResponse('No reviews found for this user.', 404);
            }

            $totalReviewsCount = $reviews->count();

            $ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            foreach ($reviews as $review) {
                if (isset($ratingCounts[$review->rating])) {
                    $ratingCounts[$review->rating]++;
                }
            }

            $ratingPercentages = [];
            foreach ($ratingCounts as $rating => $count) {
                $ratingPercentages[$rating] = $totalReviewsCount > 0 ? round(($count / $totalReviewsCount) * 100) : 0;
            }

            $data = [
                'total_reviews_count' => $totalReviewsCount,
                'rating_percentages' => $ratingPercentages,
                'reviews' => $reviews->map(function ($review) {
                    return [
                        'course_id' => $review->course_id,
                        'course_name' => $review->course ? $review->course->name : 'Course not found',
                        'user_name' => $review->user ? $review->user->name : 'User not found',
                        'user_avatar' => $review->user ? $review->user->avatar : null,
                        'review' => $review->review,
                        'rating' => (float) number_format($review->rating, 1, '.', ''),
                        'created_at' => $review->created_at->diffForHumans(),
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
        // Validate the incoming request data
        $validated = $request->validate([
            'review' => 'required|string|max:1000',
            'rating' => 'required|integer|between:0,5',
        ]);
        try {
            // Use updateOrCreate to create a new review or update the existing one
            $review = Review::updateOrCreate(
                [
                    'course_id' => $courseId,
                    'user_id' => auth()->id(),
                ],
                [
                    'review' => $request->review,
                    'rating' => $request->rating,
                ]
            );
            // Return the success response with the review data
            return Helper::jsonResponse(true, 'Review submitted successfully', 200, $review);
        } catch (Exception $e) {
            // Log the error and return an error response
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }




}
