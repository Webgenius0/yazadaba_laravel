<?php

namespace App\Http\Controllers\API\Teacher;

use App\Models\Course;
use Exception;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CourseModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Vimeo\Exceptions\VimeoUploadException;
use Vimeo\Laravel\Facades\Vimeo;
use Illuminate\Support\Str;

class CourseModuleController extends Controller
{
    public function view(): \Illuminate\Http\JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return Helper::jsonErrorResponse('User not authenticated.', 401);
            }
            if ($user->role !== 'teacher') {
                return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
            }
            $courses = $user->courses;

            if ($courses->isEmpty()) {
                return Helper::jsonResponse(false, 'No courses found for this teacher.', 404, []);
            }
            $data = CourseModule::whereIn('course_id', $courses->pluck('id'))->get();
            $data->makeHidden(['created_at', 'updated_at']);
            return Helper::jsonResponse(true, 'Course module fetched successfully', 200, $data);
        }catch (Exception $e){
            return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
        }

    }

    public function create(Request $request): \Illuminate\Http\JsonResponse
    {
    
        $userId = Auth::user();

        // Validate incoming request data
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string',
            'video_url' => 'required|mimes:mp4,avi,mov',
            'document_url' => 'nullable|mimes:pdf,docx,doc,txt,odt',
        ]);

        // Check if the user is authenticated
        if (!$userId) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        // Check if the user has the 'teacher' role
        if ($userId->role !== 'teacher') {
            return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
        }
        // Check if the user is associated with the given course
        $course = Course::find($request->course_id);
        if (!$course) {
            return Helper::jsonErrorResponse('Course not found.', 404);
        }
        // Handle video upload
        $videoUrl = '';
        $videoDuration = '00:00:00';

        if ($request->hasFile('video_url')) {
            $videoField = 'video_url';
            $videoFilePath = $request->file($videoField)->getPathname();
            try {
                // Upload video to Vimeo
                $uri = Vimeo::upload($videoFilePath, [
                    'name' => $request->title,
                    'description' => 'not available',
                    'privacy' => [
                        'view' => 'anybody'
                    ],
                    'embed' => [
                        'title' => [
                            'name' => 'hide',
                            'owner' => 'hide',
                            'portrait' => 'hide'
                        ],
                        'buttons' => [
                            'like' => false,
                            'share' => false,
                            'embed' => false
                        ],
                        'logos' => [
                            'vimeo' => false,
                        ],
                    ]
                ]);

                // Get video duration
                $moduleVideoDuration = 0;
                $retryCount = 0;
                while ($moduleVideoDuration === 0 && $retryCount < 5) {
                    sleep(5);
                    $moduleVideoData = Vimeo::request($uri, [], 'GET')['body'];
                    $moduleVideoDuration = $moduleVideoData['duration'];
                    $retryCount++;
                }

                if ($moduleVideoDuration === 0) {
                   return Helper::jsonResponse(false, 'Module video duration not found.', 404);
                }

                // Format duration (HH:MM:SS)
                $seconds = $moduleVideoDuration;
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $seconds = floor($seconds % 60);
                $videoDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

                // Store the Vimeo video URL
                $videoUrl = "https://player.vimeo.com/video/" . trim($uri, '/videos');
            } catch (VimeoUploadException $e) {
               
                Log::error('Vimeo Upload Error: ' . $e->getMessage());
               return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
            } catch (Exception $e) {
                Log::error('Error: ' . $e->getMessage());
                return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
            }
        }

        // Handle document upload (optional)
        $documentUrl = '';
        if ($request->hasFile('document_url')) {
            $file = $request->file('document_url');
            $randomString = (string)Str::uuid();
            $documentUrl = Helper::fileUpload($file, 'course/module/document', $randomString);
        }

        // Create the new course module
        $module = new CourseModule();
        $module->course_id = $request->course_id;
        $module->title = $request->title;
        $module->video_url = $videoUrl;
        $module->module_video_duration = $videoDuration;
        $module->document_url = $documentUrl ?: '';
        $module->save();

        // Return success response with the created module
        return Helper::jsonResponse(true, 'Course module created successfully', 200, $module);
    }
    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::user();

        // Validate incoming request data
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string',
            'video_url' => 'nullable|mimes:mp4,avi,mov',
            'document_url' => 'nullable|mimes:pdf,docx,doc,txt,odt',
        ]);

        if (!$userId) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        if ($userId->role !== 'teacher') {
            return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
        }
        // Check if the user is associated with the given course
        $course = Course::find($request->course_id);
        if (!$course) {
            return Helper::jsonErrorResponse('Course not found.', 404);
        }

        $module = CourseModule::find($id);
        if (!$module) {
            return Helper::jsonErrorResponse('Course module not found.', 404);
        }

        if ($request->hasFile('video_url')) {

            if ($module->video_url) {
               Helper::deleteVimeoVideo($module->video_url);
            }
            $videoUrl = '';
            $videoDuration = '00:00:00';
            $videoField = 'video_url';
            $videoFilePath = $request->file($videoField)->getPathname();

            try {
                // Upload the new video to Vimeo
                $uri = Vimeo::upload($videoFilePath, [
                    'name' => $request->title,
                    'description' => 'not available',
                    'privacy' => [
                        'view' => 'anybody',
                    ],
                    'embed' => [
                        'title' => [
                            'name' => 'hide',
                            'owner' => 'hide',
                            'portrait' => 'hide',
                        ],
                        'buttons' => [
                            'like' => false,
                            'share' => false,
                            'embed' => false,
                        ],
                        'logos' => [
                            'vimeo' => false,
                        ],
                    ],
                ]);

                // Get the video duration from Vimeo
                $moduleVideoDuration = 0;
                $retryCount = 0;
                while ($moduleVideoDuration === 0 && $retryCount < 5) {
                    sleep(5);
                    $moduleVideoData = Vimeo::request($uri, [], 'GET')['body'];
                    $moduleVideoDuration = $moduleVideoData['duration'];
                    $retryCount++;
                }

                if ($moduleVideoDuration === 0) {
                    return Helper::jsonResponse(false, 'Module video duration not found.', 404);
                }

                // Format duration (HH:MM:SS)
                $seconds = $moduleVideoDuration;
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $seconds = floor($seconds % 60);
                $videoDuration = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

                // Store the new Vimeo video URL
                $videoUrl = "https://player.vimeo.com/video/" . trim($uri, '/videos');
            } catch (VimeoUploadException $e) {
                Log::error('Vimeo Upload Error: ' . $e->getMessage());
                return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
            } catch (Exception $e) {
                Log::error('Error: ' . $e->getMessage());
                return Helper::jsonErrorResponse('An error occurred: ' . $e->getMessage(), 500);
            }

            // Update video URL and duration in the course module
            $module->video_url = $videoUrl;
            $module->module_video_duration = $videoDuration;
        }

        // Handle document upload (optional)
        if ($request->hasFile('document_url')) {
            $file = $request->file('document_url');
            $randomString = (string)Str::uuid();
            $documentUrl = Helper::fileUpload($file, 'course/module/document', $randomString);
            $module->document_url = $documentUrl;
        }

        // Update other fields
        $module->course_id = $request->course_id;
        $module->title = $request->title;
        $module->save();

        // Return success response with the updated module
        return Helper::jsonResponse(true, 'Course module updated successfully', 200, $module);
    }

    public function delete(Request $request, $moduleId): \Illuminate\Http\JsonResponse
    {
        $userId = Auth::user();

        if (!$userId) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }
        if ($userId->role !== 'teacher') {
            return Helper::jsonResponse(false, 'Access denied. User is not a teacher.', 403, []);
        }

        // Find the course module
        $module = CourseModule::find($moduleId);

        if (!$module) {
            return Helper::jsonErrorResponse('Course module not found.', 404);
        }
        if ($module->video_url) {
            Helper::deleteVimeoVideo($module->video_url);
            Helper::fileDelete($module->video_url);
        }

        if ($module->document_url) {
            Helper::fileDelete($module->document_url);
        }
        $module->delete();
        return Helper::jsonResponse(true, 'Course module and associated files deleted successfully', 200, $module);
    }

}
