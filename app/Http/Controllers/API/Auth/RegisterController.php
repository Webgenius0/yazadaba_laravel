<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:student,teacher',
        ]);
        try {
            $otp = random_int(10000, 99999);
            $otpExpiresAt = Carbon::now()->addMinutes(60);

            $user = User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'otp' => $otp,
                'otp_expires_at' => $otpExpiresAt,
                'is_otp_verified' => false,
                'role' => $request->input('role'),
            ]);

            // Send OTP email
            Mail::to($user->email)->send(new OtpMail($otp, $user, 'Verify Your Email Address'));
            return response()->json([
                'status' => true,
                'message' => 'User successfully registered. Please verify your email to log in.',
                'code' => 201,
                'data' => $user
            ], 201);

        } catch (Exception $e) {
            return Helper::jsonErrorResponse('User registration failed', 500, [$e->getMessage()]);
        }
    }

    public function VerifyEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:5',
        ]);

        try {
            $user = User::where('email', $request->input('email'))->first();

            // Check if email has already been verified
            if (!empty($user->email_verified_at)) {
                return Helper::jsonErrorResponse('Email already verified.', 409);
            }

            // Check if OTP matches
            if ((string)$user->otp !== (string)$request->input('otp')) {
                return Helper::jsonErrorResponse('Invalid OTP code', 422);
            }

            // Check if OTP has expired
            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return Helper::jsonErrorResponse('OTP has expired. Please request a new OTP.', 422);
            }

            // Verify the email
            $user->email_verified_at = now();
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            // Hide the specified fields
            $user->makeHidden([
                'avatar',
                'gender',
                'bio',
                'age',
                'phone',
                'licence_image',
                'is_verified',
                'dob'
            ]);

            // Generate the token
            $token = auth('api')->login($user);
            $tokenType = 'Bearer';

            // Return the response
            return response()->json([
                'status' => true,
                'message' => 'Email verification successful.',
                'code' => 200,
                'token' => $token,
                'token_type' => $tokenType,
                'data' => $user,
            ], 200);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), $e->getCode());
        }
    }


    public function ResendOtp(Request $request): ?\Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);
        try {
            $user = User::where('email', $request->input('email'))->first();
            if (!$user) {
                return Helper::jsonErrorResponse('User not found.', 404);
            }

            if ($user->email_verified_at) {
                return Helper::jsonErrorResponse('Email already verified.', 409);
            }

            $newOtp = random_int(10000, 99999);
            $otpExpiresAt = Carbon::now()->addMinutes(60);
            $user->otp = $newOtp;
            $user->otp_expires_at = $otpExpiresAt;
            $user->save();
            Mail::to($user->email)->send(new OtpMail($newOtp, $user, 'Verify Your Email Address'));

            return Helper::jsonResponse(true, 'A new OTP has been sent to your email.', 200);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), $e->getCode());
        }
    }


}
