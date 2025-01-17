<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use App\Models\User;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function Login(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
            'role'     => 'required|string|in:student,teacher,admin',
        ]);

        try {
            // Check if email is valid
            if (filter_var($request->email, FILTER_VALIDATE_EMAIL) !== false) {
                $user = User::withTrashed()->where('email', $request->email)->first();
                if (empty($user)) {
                    return Helper::jsonErrorResponse('User not found', 404);
                }

                // Check if the role matches the one in the request
                if ($user->role !== $request->role) {
                    return Helper::jsonErrorResponse('User role does not match the requested role', 403);
                }
                if ($user->email_verified_at) {
                    $user->is_verified = true;
                    $user->save();
                }
            }

            // Check the password
            if (!Hash::check($request->password, $user->password)) {
                return Helper::jsonErrorResponse('Invalid password', 401);
            }

            // Check if the email is verified before login is successful
            if (!$user->email_verified_at) {
                return Helper::jsonErrorResponse('Email not verified. Please verify your email before logging in.', 403);
            }

            // Generate token if email is verified and role matches
            $token = auth('api')->login($user);

            return response()->json([
                'status'     => true,
                'message'    => 'User logged in successfully.',
                'code'       => 200,
                'token_type' => 'bearer',
                'token'      => $token,
                'expires_in' => auth('api')->factory()->getTTL() * 60,
                'data'       => auth('api')->user(),
            ], 200);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }


    public function refreshToken(): \Illuminate\Http\JsonResponse
    {
        $refreshToken = auth('api')->refresh();

        return response()->json([
            'status'     => true,
            'message'    => 'Access token refreshed successfully.',
            'code'       => 200,
            'token_type' => 'bearer',
            'token'      => $refreshToken,
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'data' => auth('api')->user()->load('personalizedSickle')
        ]);
    }
}
