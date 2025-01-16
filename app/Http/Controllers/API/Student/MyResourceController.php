<?php

namespace App\Http\Controllers\API\Student;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CourseEnroll;
use App\Models\ISComplete;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MyResourceController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }
            if ($user->role !== 'student') {
                return Helper::jsonResponse(false, 'Access denied. User is not a student.', 403, []);
            }

            // Fetch the courses where the user is enrolled and the status is completed
            $myResources = CourseEnroll::where('user_id', $user->id)
                ->with(['course' => function ($query) {
                    $query->select('id', 'name', 'category_id', 'cover_image', 'course_duration')
                        ->with('courseModules');
                }])
                ->get();
            $ongoingCourses = [];
            $completedCourses = [];

            $myResources->each(function ($enrollment) use ($user, &$ongoingCourses, &$completedCourses) {
                $totalModules = $enrollment->course->courseModules->count();
                $completedModules = $enrollment->course->isCompletes()
                    ->where('user_id', $user->id)
                    ->where('status', 'complete')
                    ->count();
                $completionPercentage = $totalModules ? ($completedModules / $totalModules) * 100 : 0;

                // Calculate total duration in seconds
                $totalDurationInSeconds = $enrollment->course->courseModules->sum(function ($module) {
                    $durationParts = explode(':', $module->module_video_duration);
                    return ($durationParts[0] * 3600) + ($durationParts[1] * 60) + $durationParts[2];
                });

                $formattedDuration = $totalDurationInSeconds < 60 ? "{$totalDurationInSeconds} sec" : ($totalDurationInSeconds < 3600 ? floor($totalDurationInSeconds / 60) . " min" : floor($totalDurationInSeconds / 3600) . " hours");

                // Identify if the course is ongoing or completed
                $ongoing = $completionPercentage > 0 && $completionPercentage < 100;
                $completed = $completionPercentage === 100;

                // Controller action after course completion
                $certificateImage = Helper::generateCertificateWithDynamicName($user, $enrollment->course);
                Certificate::create([
                    'user_id' => $user->id,
                    'course_id' => $enrollment->course->id,
                    'certificate_image' => $certificateImage,
                ]);

                // Get lesson details
                $lessons = $enrollment->course->courseModules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'title' => $module->title,
                        'duration' => $module->module_video_duration,
                        'video_url' => $module->video_url,
                        'document_url' => $module->document_url,
                    ];
                });

                // Check ongoing course
                if ($ongoing) {
                    $ongoingCourses[] = [
                        'id' => $enrollment->course->id,
                        'name' => $enrollment->course->name,
                        'cover_image' => $enrollment->course->cover_image,
                        'course_duration' => $formattedDuration,
                        'completion_percentage' => round($completionPercentage, 1),
                        'certificate_image' => url($certificateImage),
                        'lessons' => $lessons,
                    ];
                }

                // Check completed course
                if ($completed) {
                    $completedCourses[] = [
                        'id' => $enrollment->course->id,
                        'name' => $enrollment->course->name,
                        'cover_image' => $enrollment->course->cover_image,
                        'course_duration' => $formattedDuration,
                        'completion_percentage' => round($completionPercentage, 1),
                        'certificate_image' => url($certificateImage),
                        'lessons' => $lessons,
                    ];
                }
            });

            // Return the response
            return Helper::jsonResponse(true, 'Resource retrieved successfully.', 200, [
                'ongoing' => $ongoingCourses,
                'complete' => $completedCourses,
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
        }
    }
}
