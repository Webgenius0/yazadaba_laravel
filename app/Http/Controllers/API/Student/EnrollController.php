<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnroll;
use App\Notifications\EnrollNotification;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EnrollController extends Controller
{
    public function enroll(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate incoming request data
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);
        $user = auth()->user();
        // Check if the user is authenticated
        if (!$user) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        // Check if the user is a student
        if ($user->role !== 'student') {
            return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
        }
        // Fetch the course
        $course = Course::where('status','active')->find($request->course_id);

        if (!$course) {
            return Helper::jsonErrorResponse('Course not found.', 404);
        }

        // Check if the user has already enrolled in the course
        $existingEnrollment = CourseEnroll::where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($existingEnrollment) {
            return Helper::jsonResponse(false, 'User is already enrolled in this course.', 400, []);
        }

        $coursePrice = $course->price;

        // Create a new course enrollment record
        $enroll = CourseEnroll::create([
            'user_id' => $user->id,
            'course_id' => $request->course_id,
            'amount' => $coursePrice,
            'transaction_id' => Str::random(20),
            'status' => 'completed',
        ]);
        // Notify the user about enrollment
        $user->notify(new EnrollNotification($course));
        if ($user->firebaseTokens) {
            // Prepare the notification data
            $notifyData = [
                'title' => 'Course Enrollment Success',
                'body' => "You have successfully enrolled in the course '{$course->name}'. Start learning today!",
            ];
            foreach ($user->firebaseTokens as $firebaseToken) {
                Helper::sendNotifyMobile($firebaseToken->token, $notifyData);
            }
        }
        // Return success response
        return Helper::jsonResponse(true, 'User successfully enrolled in the course.', 200, $enroll);
    }
}
