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
        $request->validate([
            'token'    => 'required',
            'role'     => 'required|in:teacher,student',
            'provider' => 'required|in:google,facebook,apple',
        ]);

        try {
            $provider   = $request->provider;
            $role       = $request->role;
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($request->token);

            if ($socialUser) {
                $user = User::withTrashed()
                    ->where('email', $socialUser->getEmail())
                    ->orWhere(function ($query) use ($provider, $socialUser) {
                        $query->where('provider', $provider)
                            ->where('provider_id', $socialUser->getId());
                    })
                    ->first();

                if (!empty($user->deleted_at)) {
                    return Helper::jsonErrorResponse('Your account has been deleted.', 410);
                }

                $isNewUser = false;

                if (!$user) {
                    $password = Str::random(16);
                    $user     = User::create([
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

                Auth::login($user);
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
                return Helper::jsonErrorResponse('Unauthorized', 401);
            }
        } catch (Exception $e) {
            return Helper::jsonErrorResponse('Something went wrong', 500, ['error' => $e->getMessage()]);
        }
    }
}
