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
        //validation request
        $request->validate([
            'token'    => 'required',
            'role'     => 'required|in:teacher,student',
            'provider' => 'required|in:google,facebook,apple',
        ]);

        try {
            $provider = $request->provider;
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($request->token);

            if ($socialUser) {
                $user = User::withTrashed()->where('email', $socialUser->email)->first();

                // Check if the user is soft deleted
                if ($user && !empty($user->deleted_at)) {
                    return Helper::jsonErrorResponse('Your account has been deleted.', 410);
                }

                // If no user found, create a new user
                if (!$user) {
                    $password = Str::random(16);
                    $user = User::create([
                        'name'              => $socialUser->getName(),
                        'email'             => $socialUser->getEmail(),
                        'password'          => bcrypt($password),
                        'avatar'            => $socialUser->getAvatar(),
                        'email_verified_at' => now(),
                        'role'              => $request->role,
                    ]);
                }

                // Login the user
                Auth::login($user);

                // Create JWT token for the user
                $token = auth('api')->login($user);

                return response()->json([
                    'status'     => true,
                    'message'    => 'User logged in successfully.',
                    'code'       => 200,
                    'token_type' => 'bearer',
                    'token'      => $token,
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'data'       => $user,
                ], 200);
            } else {
                return Helper::jsonResponse(false, 'Unauthorized', 401);
            }
        } catch (Exception $e) {
            return Helper::jsonResponse(false, 'Something went wrong', 500, ['error' => $e->getMessage()]);
        }
    }

}
