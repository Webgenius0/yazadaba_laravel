<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\NoReturn;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;

class SocialLoginController extends Controller
{
    public function RedirectToProvider($provider): \Symfony\Component\HttpFoundation\RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver($provider)->redirect();
    }

     public function HandleProviderCallback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        dd($socialUser);
    }
    public function SocialLogin(Request $request): \Illuminate\Http\JsonResponse
    {
        // Validate the request parameters
        $request->validate([
            'token'    => 'required',
            'role'     => 'required|in:teacher,student',
            'provider' => 'required|in:google,facebook,apple',
        ]);

        try {
            $provider   = $request->provider;
            $role       = $request->role;
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($request->token);

            // Check if the social user data is retrieved successfully
            if ($socialUser) {
                // Check if the user exists in the database (either by email or social provider)
                $user = User::withTrashed()
                    ->where('email', $socialUser->getEmail())
                    ->orWhere(function ($query) use ($provider, $socialUser) {
                        $query->where('provider', $provider)
                            ->where('provider_id', $socialUser->getId());
                    })
                    ->first();

                // Check if the account is deleted (soft delete check)
                if (!empty($user->deleted_at)) {
                    return Helper::jsonErrorResponse('Your account has been deleted.', 410);
                }

                // If user is found, proceed with login logic
                if ($user) {
                    // If the role is 'teacher', log the user in
                    if ($role === 'teacher') {
                        Auth::login($user);
                        return Helper::jsonResponse('Login successful', 200);
                    }

                    // If the email is already taken and role does not match, return an error
                    return Helper::jsonErrorResponse('Email already taken', 400);
                }

                // If the user is not found, check if it's a new user
                $isNewUser = false;
                if (!$user) {
                    // Generate a random password for the new user (because social login does not require password)
                    $password = Str::random(16);
                    // Create a new user in the database
                    $user = User::create([
                        'name'              => $socialUser->getName(),
                        'email'             => $socialUser->getEmail(),
                        'password'          => bcrypt($password),
                        'avatar'            => $socialUser->getAvatar(),
                        'provider'          => $provider,
                        'provider_id'       => $socialUser->getId(),
                        'role'              => $role,
                        'email_verified_at' => now(),
                    ]);
                    $isNewUser = true;
                }

                // If the user is new
                if ($isNewUser) {
                    // For 'student', send a verification email
                    if ($role === 'student') {
                        // Send verification email code here
                        return Helper::jsonResponse('Verification email sent', 200);
                    }

                    // For 'teacher', create a new teacher and log them in
                    if ($role === 'teacher') {
                        $newUser = User::create([
                            'email' => $socialUser->getEmail(),
                            'name' => $socialUser->getName(),
                            'role' => 'teacher',
                            'password' => bcrypt(Str::random(16)), // Random password generation
                        ]);

                        // Log in the newly created teacher
                        Auth::login($newUser);
                        return Helper::jsonResponse('Teacher created and logged in', 200);
                    }
                }

                // If the user is already registered and login is successful, return the response
                $token = Auth::login($user); // Generate a token for the user

                return response()->json([
                    'status'     => true,
                    'message'    => 'User logged in successfully.',
                    'code'       => 200,
                    'token_type' => 'bearer',
                    'token'      => $token,
                    'expires_in' => auth('api')->factory()->getTTL() * 60, // Token expiration time in minutes
                    'data'       => $user,
                ], 200);
            } else {
                // If social login fails (unauthorized)
                return Helper::jsonErrorResponse('Unauthorized', 401);
            }
        } catch (Exception $e) {
            // Catch any exceptions and return error response
            return Helper::jsonErrorResponse('Something went wrong', 500, ['error' => $e->getMessage()]);
        }
    }

}
