<?php

namespace App\Http\Controllers\API\Auth;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\NoReturn;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{

    public function TeacherProfile(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user is authenticated and is a teacher
            if (!$user) {
                return Helper::jsonResponse(false, 'Teacher not found', 404, []);
            }
            if ($user->role !== 'teacher') {
                return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
            }

            return Helper::jsonResponse(true, 'Teacher data Fetch Successfully', 200, $user);

        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    // Upload avatar for teacher
    public function TeacherUploadAvatar(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        try {
            $user = auth('api')->user();

            // Check if user is authenticated and is a teacher
            if (!$user) {
                return Helper::jsonResponse(false, 'Teacher not found', 404, []);
            }
            if ($user->role !== 'teacher') {
                return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
            }

            if ($request->hasFile('avatar')) {
                $randomString = (string)Str::uuid();
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();

                // Delete the old avatar if it exists
                if (!empty($user->avatar)) {
                    Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
                }

                // Upload new avatar with UUID and extension
                $avatarPath = Helper::fileUpload($file, 'user/avatar', $randomString . '.' . $extension);
                $user->avatar = $avatarPath;
                $user->save();
            }

            return Helper::jsonResponse(true, 'Avatar uploaded successfully!', 200, $user);

        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    // Update teacher profile
    public function TeacherUpdateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'fname' => 'nullable|string|max:255',
            'lname' => 'nullable|string|max:255',
            'bio' => 'nullable|max:500',
            'gender' => 'nullable|in:male,female',
            'dob' => 'nullable|date|before_or_equal:today',
            'email' => 'nullable|string|max:255|unique:users,email,' . Auth::id(),
            'phone' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:18|max:100',
            'licence_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        try {
            $user = auth('api')->user();

            // Check if user is authenticated and is a teacher
            if (!$user) {
                return Helper::jsonResponse(false, 'Teacher not found', 404, []);
            }

            if ($user->role !== 'teacher') {
                return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
            }

            // Initialize the path for the license image
            $licencePath = '';

            // Handle file upload if there is a licence image
            if ($request->hasFile('licence_image')) {
                $file = $request->file('licence_image');
                $randomString = (string)Str::uuid();
                $licencePath = Helper::fileUpload($file, 'teacher/licence', $randomString);
            }

            // Update the user's profile with validated data
            $user->update([
                'fname' => $request->input('fname', $user->fname),
                'lname' => $request->input('lname', $user->lname),
                'bio' => $request->input('bio', $user->bio),
                'age' => $request->input('age', $user->age),
                'gender' => $request->input('gender', $user->gender ?? null),
                'dob' => $request->input('dob', $user->dob),
                'email' => $request->input('email', $user->email),
                'phone' => $request->input('phone', $user->phone),
                'licence_image' => $licencePath ?: $user->licence_image,
            ]);

            return Helper::jsonResponse(true, 'Profile updated successfully!', 200, $user);

        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
    public function TeacherDeleteProfile(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user is authenticated and is a teacher
            if (!$user) {
                return Helper::jsonResponse(false, 'Teacher not found', 404, []);
            }
            if ($user->role !== 'teacher') {
                return Helper::jsonResponse(false, 'Access denied. User is not a Teacher.', 403, []);
            }

            // Find the authenticated teacher and permanently delete the profile
            $teacherDelete = User::find($user->id);
            if ($teacherDelete) {
                // Use forceDelete() to permanently remove the user
                $teacherDelete->forceDelete();
            }

            return Helper::jsonResponse(true, 'Teacher Profile deleted permanently!', 200, []);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonResponse(false, 'Teacher Profile delete failed!', 500);
        }
    }

    public function StudentProfile(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user is authenticated and is a teacher
            if (!$user) {
                return Helper::jsonResponse(false, 'Student not found', 404, []);
            }
            if ($user->role !== 'student') {
                return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
            }

            return Helper::jsonResponse(true, 'Student data Fetch Successfully', 200, $user);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    // Upload avatar for Student
    public function StudentUploadAvatar(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        try {
            $user = auth('api')->user();

            // Check if user is authenticated and is a teacher
            if (!$user) {
                return Helper::jsonResponse(false, 'Student not found', 404, []);
            }
            if ($user->role !== 'student') {
                return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
            }

            if ($request->hasFile('avatar')) {
                $randomString = (string)Str::uuid();
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();

                // Delete the old avatar if it exists
                if (!empty($user->avatar)) {
                    Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
                }

                // Upload new avatar with UUID and extension
                $avatarPath = Helper::fileUpload($file, 'student/avatar', $randomString . '.' . $extension);
                $user->avatar = $avatarPath;
                $user->save();
            }

            return Helper::jsonResponse(true, 'Avatar uploaded successfully!', 200, $user);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    // Update teacher profile
    public function StudentUpdateProfile(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'fname' => 'nullable|string|max:255',
            'lname' => 'nullable|string|max:255',
            'bio' => 'nullable|max:500',
            'gender' => 'nullable|in:male,female',
            'dob' => 'nullable|date|before_or_equal:today',
            'email' => 'nullable|string|max:255|unique:users,email,' . Auth::id(),
            'age' => 'nullable|integer|min:18|max:100',

        ]);

        try {
            $user = auth('api')->user();

            // Check if user is authenticated and is a teacher
            if (!$user) {
                return Helper::jsonResponse(false, 'Student not found', 404, []);
            }

            if ($user->role !== 'student') {
                return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
            }

            // Update the user's profile with validated data
            $user->update([
                'fname' => $request->input('fname', $user->fname),
                'lname' => $request->input('lname', $user->lname),
                'bio' => $request->input('bio', $user->bio),
                'age' => $request->input('age', $user->age),
                'gender' => $request->input('gender', $user->gender ?? null),
                'dob' => $request->input('dob', $user->dob),
                'email' => $request->input('email', $user->email),

            ]);

            return Helper::jsonResponse(true, 'Profile updated successfully!', 200, $user);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function StudentDeleteProfile(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();

            // Check if user is authenticated and is a student
            if (!$user) {
                return Helper::jsonResponse(false, 'Student not found', 404, []);
            }
            if ($user->role !== 'student') {
                return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
            }

            // Find the authenticated student and permanently delete the profile
            $studentDelete = User::find($user->id);
            if ($studentDelete) {
                // Use forceDelete() to permanently remove the user
                $studentDelete->forceDelete();
            }

            return Helper::jsonResponse(true, 'Student Profile deleted permanently!', 200, []);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonResponse(false, 'Student Profile delete failed!', 500);
        }
    }

    
}
