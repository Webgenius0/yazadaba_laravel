<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ISComplete;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IsCompleteController extends Controller
{
    public function isComplete(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        // Validate the incoming request
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'course_module_id' => 'required|exists:course_modules,id',
            'status' => 'required|in:complete',
        ]);

        // Check if the user is authenticated
        if (!$user) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        // Check if the user is a student
        if ($user->role !== 'student') {
            return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
        }

        // Check if the user is enrolled in the course
        $isEnrolled = DB::table('course_enrolls')
            ->where('user_id', $user->id)
            ->where('course_id', $request->course_id)
            ->whereIn('status', ['completed'])
            ->exists();

        if (!$isEnrolled) {
            return Helper::jsonErrorResponse('User is not enrolled in this course.', 400);
        }

        // Attempt to create or update the completion record
        try {
            $data = ISComplete::CreateOrUpdate([
                'user_id' => $user->id,
                'course_id' => $request->course_id,
                'course_module_id' => $request->course_module_id,
                'status' => 'complete',
            ]);

            // Return success response with the created or updated data
            return Helper::jsonResponse(true, 'Course module marked as complete successfully.', 200, $data);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }

}
