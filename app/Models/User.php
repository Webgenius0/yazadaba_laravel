<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property mixed|string|null $avatar
 * @property int|mixed $age
 * @property mixed $lname
 * @property mixed $fname
 * @property mixed $bio
 * @property mixed $email
 * @property mixed $phone
 * @property mixed $dob
 * @property mixed $licence_image
 * @property mixed $teacherData
 * @property mixed $role
 * @property mixed $id
 * @property mixed $name
 * @property mixed $courses
 * @property mixed $firebaseTokens
 * @method teacher()
 * @method static create(array $array)
 * @method static where(string $string, mixed $input)
 * @method static find(mixed $id)
 */
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes,Notifiable;

    public mixed $gender;

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    //local scope
    public function scopeTeacher(Builder $query): Builder
    {
        return $query->where('role', 'teacher');
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function firebaseTokens(): HasMany
    {
        return $this->hasMany(FirebaseToken::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
   protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'reset_password_token',
        'reset_password_token_expire_at',
        'is_otp_verified',
        'deleted_at',
        'created_at',
        'updated_at',
        'role',
        'status',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'is_otp_verified' => 'boolean',
            'reset_password_token_expires_at' => 'datetime',
            'password' => 'hashed',
             'gender' => 'string',
        ];
    }

    public function getAvatarAttribute($value): string|null
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
    public function getLicenceImageAttribute($value): string|null
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
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }


}
