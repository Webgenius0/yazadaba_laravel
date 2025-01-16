<?php

namespace App\Helpers;

use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Vimeo\Laravel\Facades\Vimeo;


class Helper {
    //! File or Image Upload
    public static function fileUpload($file, string $folder, string $name): ?string {
        $imageName = Str::slug($name) . '.' . $file->extension();
        $path      = public_path('uploads/' . $folder);
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
        $file->move($path, $imageName);
        return 'uploads/' . $folder . '/' . $imageName;
    }
    //! File or Image Delete
    public static function fileDelete(string $path): void {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    //! Generate Slug
    public static function makeSlug($model, string $title): string {
        $slug = Str::slug($title);
        while ($model::where('slug', $slug)->exists()) {
            $randomString = Str::random(5);
            $slug         = Str::slug($title) . '-' . $randomString;
        }
        return $slug;
    }

    //! JSON Response
    public static function jsonResponse(bool $status, string $message, int $code, $data = null,bool $paginate = false,$paginateData = null): JsonResponse {
        $response = [
            'status'  => $status,
            'message' => $message,
            'code'    => $code,
        ];
        if ($paginate && !empty($paginateData)) {
            $response['data'] = $data;
            $response['pagination'] = [
                'current_page' => $paginateData->currentPage(),
                'last_page' => $paginateData->lastPage(),
                'per_page' => $paginateData->perPage(),
                'total' => $paginateData->total(),
                'first_page_url' => $paginateData->url(1),
                'last_page_url' => $paginateData->url($paginateData->lastPage()),
                'next_page_url' => $paginateData->nextPageUrl(),
                'prev_page_url' => $paginateData->previousPageUrl(),
                'from' => $paginateData->firstItem(),
                'to' => $paginateData->lastItem(),
                'path' => $paginateData->path(),
            ];
        }elseif ($paginate && !empty($data)){
            $response['data'] = $data->items();
            $response['pagination'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'first_page_url' => $data->url(1),
                'last_page_url' => $data->url($data->lastPage()),
                'next_page_url' => $data->nextPageUrl(),
                'prev_page_url' => $data->previousPageUrl(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'path' => $data->path(),
            ];
        }elseif($data !== null){
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    public static function jsonErrorResponse(string $message, int $code = 400, array $errors = []): JsonResponse
    {
        $response = [
            'status'  => false,
            'message' => $message,
            'code'    => $code,
            'errors'  => $errors,
        ];
        return response()->json($response, $code);
    }

    public static function deleteVimeoVideo(string $videoUrl): void
    {
        // Extract the video ID from the Vimeo URL
        $videoId = basename($videoUrl);

        // Send a DELETE request to Vimeo API to remove the video
        try {
            $response = Vimeo::request("/videos/{$videoId}", [], 'DELETE');
            if ($response['status'] !== 200) {
                Log::error("Failed to delete Vimeo video with ID {$videoId}");
            }
        } catch (Exception $e) {
            Log::error("Error deleting Vimeo video: " . $e->getMessage());
        }
    }

    //generate certificate

    /**
     * @throws Exception
     */
    public static function generateCertificateWithDynamicName($user, $course): string
    {
        try {
            // Generate the PDF content using a Blade template
            $pdf = PDF::loadView('certificates.template', compact('user', 'course'));

            // Generate a dynamic unique filename for the certificate
            $certificateFileName = uniqid('certificate_', true) . '.pdf';

            // Define the path to store the PDF file in 'public/uploads/certificates'
            $certificatePath = public_path('uploads/certificates/' . $certificateFileName);

            // Save the generated PDF to the specified path
            $pdf->save($certificatePath);

            // Optionally, you can also store the filename in the database to associate with the certificate
            return 'uploads/certificates/' . $certificateFileName;  // Return the relative file path
        } catch (Exception $e) {
            // Log any errors that occur during certificate generation
            Log::error('Certificate Generation Error: ' . $e->getMessage());

            // Re-throw the exception to be handled by the caller
            throw $e;
        }
    }

    public static function sendNotifyMobile($token, $notifyData): void
    {
        try {
            $factory = (new Factory)->withServiceAccount(storage_path(env('FIREBASE_CREDENTIALS')));
            $messaging = $factory->createMessaging();
            $notification = Notification::create($notifyData['title'], Str::limit($notifyData['body'], 100));
            $message = CloudMessage::withTarget('token', $token)->withNotification($notification);
            $messaging->send($message);
        } catch (Exception $exception) {
            Log::error($exception->getMessage());
        } catch (MessagingException $e) {
            Log::error($e->getMessage());
        }
        return;
    }

}
