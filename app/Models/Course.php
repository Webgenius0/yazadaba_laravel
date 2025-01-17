<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static find($id)
 * @method static create(array $array)
 * @method static where(string $string, mixed $id)
 * @method static take(int $int)
 * @method static withCount(string $string)
 */
class Course extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price' => 'double',
    ];
    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo{
        return $this->belongsTo(User::class);
    }
    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function isCompletes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ISComplete::class);
    }

    public function gradeLevel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function courseModules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CourseModule::class, 'course_id');
    }

    public function getCoverImageAttribute($value): string|null
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        // Check if the request is an API request
        if (request()->is('api/*') && !empty($value)) {
            // Return the full URL for API requests
            return url($value);
        }

        // Return only the path for web requests
        return $value;
    }

    

}
