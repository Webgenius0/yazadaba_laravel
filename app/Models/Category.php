<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $name
 * @method static create(array $array)
 * @method static findOrFail($id)
 * @method static orderBy(string $string, string $string1)
 */
class Category extends Model
{

    protected $guarded = [];

    public function courses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Course::class);
    }

}
