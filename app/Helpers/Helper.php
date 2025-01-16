<?php

namespace App\Helpers;

use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Vimeo\Laravel\Facades\Vimeo;


class Helper {
    //! File or Image Upload
    public static function fileUpload($fileContent, string $folder, string $name): ?string
    {
        // Create a slug for the file name
        $imageName = Str::slug($name) . '.pdf';
        $path      = public_path('uploads/' . $folder);

        // Ensure the directory exists
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true) && !is_dir($path)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
            }
        }
        // Save the raw PDF content to a file
        $filePath = $path . '/' . $imageName;
        file_put_contents($filePath, $fileContent);  // Save the PDF content

        // Return the relative file path
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
    public static  function generateCertificateWithDynamicName($user, $course): string
    {
        try {
            $pdf = PDF::loadView('certificates.template', compact('user', 'course'));
            $certificateFileName = uniqid('certificate_', true) . '.pdf';
            return self::fileUpload($pdf->output(), 'certificates', $certificateFileName);
        } catch (Exception $e) {
            Log::error('Certificate Generation Error: ' . $e->getMessage());
            throw $e;
        }
    }

}
