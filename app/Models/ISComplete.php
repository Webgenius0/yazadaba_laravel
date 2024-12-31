<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, int|string|null $id)
 */
class ISComplete extends Model
{
    protected $guarded = [];

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function courseModule(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CourseModule::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function CreateOrUpdate(array $data)
    {
        return self::updateOrCreate(
        // Define the unique combination of fields to check for an existing record
            [
                'user_id' => $data['user_id'],
                'course_id' => $data['course_id'],
                'course_module_id' => $data['course_module_id']
            ],
            // Fields to update if a record exists, or set if creating a new one
            [
                'status' => $data['status']
            ]
        );
    }

}
