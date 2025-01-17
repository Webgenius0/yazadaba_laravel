<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static find($moduleId)
 * @method static where(string $string, mixed $id)
 * @method static whereIn(string $string, $pluck)
 * @property mixed $course_id
 * @property mixed|string|null $video_url
 * @property mixed $title
 * @property mixed|string|null $document_url
 * @property float|mixed|string $module_video_duration
 */
class CourseModule extends Model
{
    protected $guarded = [];

    public function getVideoUrlAttribute($value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            // Return the full URL if it's already a valid URL
            return $value;
        }

        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url('' . $value);
        }
        // Return only the path for web requests
        return asset('' . $value);
    }
    public function getDocumentUrlAttribute($value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            // Return the full URL if it's already a valid URL
            return $value;
        }

        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url('/' . $value);
        }
        // Return only the path for web requests
        return asset('/' . $value);
    }
    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class , 'course_id');
    }

    public function isCompletes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ISComplete::class);
    }




}
