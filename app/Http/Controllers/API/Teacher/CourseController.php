<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\GradeLevel;
use App\Models\PublishRequest;
use App\Models\User;
use App\Notifications\PublishRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;
use App\Models\CourseEnroll;
use Illuminate\Support\Facades\Notification;


class CourseController extends Controller
{
    public function view(): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::user();
        if (!$userId) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }
        if ($userId->role !== 'teacher') {
            return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
        }

        $data = Course::where('user_id', $userId->id)->get();
        $data->makeHidden(['created_at', 'updated_at', 'deleted_at']);
        return Helper::jsonResponse(true, 'Course Data Fetch successfully', 200, $data);
    }

    public function create(Request $request): \Illuminate\Http\JsonResponse
    {
        // Get the authenticated user's ID
        $userId = Auth::user();

        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'description' => 'required|max:500',
            'price' => 'required|numeric|min:0',
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        // Check if the user is authenticated
        if (!$userId) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }
        if ($userId->role !== 'teacher') {
            return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
        }

        $coverImage = '';
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $randomString = (string)Str::uuid();
            $coverImage = Helper::fileUpload($file, 'course/cover-image', $randomString);
        }

        // Create the course with the authenticated user's ID
        $course = Course::create([
            'user_id' => $userId->id,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'grade_level_id' => $request->grade_level_id,
            'description' => $request->description,
            'price' => $request->price,
            'cover_image' => $coverImage,
        ]);

        // Return a successful response with the created course
        return Helper::jsonResponse(true, 'Course created successfully', 200, $course);
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {

        $userId = Auth::user();

        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'grade_level_id' => 'required|exists:grade_levels,id',
            'description' => 'required|max:500',
            'price' => 'required|numeric|min:0',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        // Check if the user is authenticated
        if (!$userId) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }
        if ($userId->role !== 'teacher') {
            return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
        }

        $course_update = Course::find($id);
        if (!$course_update) {
            return Helper::jsonErrorResponse('Course not found.', 404);
        }
        $coverImage = '';
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $randomString = (string)Str::uuid();
            $coverImage = Helper::fileUpload($file, 'course/cover-image', $randomString);
        }

        $course_update->name = $request->name;
        $course_update->category_id = $request->category_id;
        $course_update->grade_level_id = $request->grade_level_id;
        $course_update->description = $request->description;
        $course_update->price = $request->price;
        $course_update->cover_image = $coverImage;
        $course_update->save();

        return Helper::jsonResponse(true, 'Course updated successfully', 200, $course_update);
    }
    public function delete($id): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::id();
        $data = Course::find($id);
        if (!$data) {
            return Helper::jsonErrorResponse('Course not found.', 404);
        }

        // Check if the course is already enrolled
        $enrollCourse = CourseEnroll::where('course_id', $data->id)->exists();
        if ($enrollCourse) {
            return Helper::jsonErrorResponse('Course already enrolled.', 400);
        }
        // Delete related course modules
        CourseModule::where('course_id', $data->id)->delete();
        // Delete the cover image if it exists
        if ($data->cover_image) {
            Helper::fileDelete($data->cover_image);
        }
        // Delete the course
        $data->delete();

        return Helper::jsonResponse(true, 'Course deleted successfully', 200, $data);
    }

    //view all category
    public function getCategories(): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::user();
        try {
            if (!$userId) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }
            if ($userId->role !== 'teacher') {
                return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
            }
            $categories = Category::all();
            return Helper::jsonResponse(true, 'Categories fetch successfully', 200, $categories);
        } catch (\Exception $e) {
            return Helper::jsonResponse(false, 'Something went wrong.', 500);
        }
    }

    public function getGradeLevel(): \Illuminate\Http\JsonResponse
    {
        $userId = auth()->user();
        try {
            if (!$userId) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }
            if ($userId->role !== 'teacher') {
                return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
            }
            $gradeLevel = GradeLevel::all();
            return Helper::jsonResponse(true, 'Grade Level fetch successfully', 200, $gradeLevel);
        } catch (\Exception $e) {
            return Helper::jsonResponse(false, 'Something went wrong.', 500);
        }
    }

    public function TogglePublished(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        try {
            $userId = Auth::id();
            $course = Course::where('user_id', $userId)->find($id);
            // Check if course exists
            if (!$course) {
                return response()->json(['success' => false, 'message' => 'Course not found.'], 404);
            }
            // Validate inputs
            $request->validate([
                'publish_status' => 'required|in:active,inactive',
            ]);
            $publishStatus = $request->input('publish_status');
            // Check if the course is already in the requested publish status
            if ($course->status === $publishStatus) {
                return Helper::jsonResponse(false, 'The course is already ' . $publishStatus . '. No changes needed.', 400);
            }
            // Check if there is already a pending publish request for this course and user
            $existingPublishRequest = PublishRequest::where('user_id', $userId)
                ->where('course_id', $id)
                ->where('status', 'pending')
                ->first();
            if ($existingPublishRequest) {
                // If there's already a pending request, don't send a notification and return a message
                return Helper::jsonResponse(false, 'You have already submitted a pending request for this course.', 400);
            }
            // Create or update the publish request
            $publishRequest = PublishRequest::updateOrCreate(
                ['user_id' => $userId, 'course_id' => $id],
                [
                    'publish_status' => $publishStatus,
                    'status' => 'pending',
                ]
            );
            // Notify admins about the request only if it's a new request
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new PublishRequestNotification($publishRequest));
            // Construct the appropriate message based on the publish status
            $message = $publishStatus === 'active'
                ? 'Your request to publish the course is pending admin approval.'
                : 'Your request to unpublish the course is pending admin approval.';

            // Send notification to the user
            $user = auth()->user();
            if ($user->firebaseTokens) {
                $notifyData = [
                    'title' => 'Course Publish Request Submitted',
                    'body' => $message,
                ];
                foreach ($user->firebaseTokens as $firebaseToken) {
                    Helper::sendNotifyMobile($firebaseToken->token, $notifyData);
                }
            }

            return Helper::jsonResponse(true, $message, 200, $publishRequest);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Something went wrong.'], 500);
        }
    }


    public function myResource(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user || $user->role != 'teacher') {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }

            // Step 1: Fetch all courses for the specific teacher (user)
            $courses = Course::where('user_id', $user->id)->get();

            // Step 2: Loop through each course and count the number of students enrolled
            $responseData = $courses->map(function ($course) {
                // Count the number of students enrolled in each course
                $enrollsCount = CourseEnroll::where('course_id', $course->id)->count();
                return [

                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'enrolled_students' => $enrollsCount,
                    'price' => $course->price ?? '0.0',
                    'cover_image' => $course->cover_image ?? '',
                    'status' => $course->status
                ];
            });

            // Step 3: Return a combined response with all course data
            return Helper::jsonResponse(true, 'Resource Data fetched successfully', 200, $responseData);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

}
