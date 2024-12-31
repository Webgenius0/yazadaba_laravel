<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static whereIn(string $string, \Closure $param)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 * @method static where(string $string, $id)
 * @method static createOrUpdate(array $array)
 * @method static updateOrCreate(array $array, array $array1)
 */
class Review extends Model
{
    protected $guarded = [];

    public function course(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }



}
