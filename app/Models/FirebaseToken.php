<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, mixed $id)
 * @property int|mixed $user_id
 * @property mixed $token
 * @property mixed $device_id
 * @property mixed|string $status
 */
class FirebaseToken extends Model
{

    protected $guarded = [];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
