<?php

namespace App\Http\Controllers\API\Auth;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\OtpMail;
use App\Helpers\Helper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    public function forgotPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            $email = $request->input('email');
            $otp = random_int(10000, 99999);
            $user = User::where('email', $email)->first();

            if ($user) {
                Mail::to($email)->send(new OtpMail($otp, $user, 'Reset Your Password'));
                $user->update([
                    'otp' => $otp,
                    'otp_expires_at' => Carbon::now()->addMinutes(60),
                ]);
                return Helper::jsonResponse(true, 'OTP Code Sent Successfully Please Check Your Email.', 200);
            } else {
                return Helper::jsonErrorResponse('Invalid Email Address', 404);
            }
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function VerifyOTP(Request $request): \Illuminate\Http\JsonResponse
    {

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:5',
        ]);

        try {
            $email = $request->input('email');
            $otp = $request->input('otp');
            $user = User::where('email', $email)->first();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            }

            if (Carbon::parse($user->otp_expires_at)->isPast()) {
                return Helper::jsonErrorResponse('OTP has expired.', 400);
            }

            if ($user->otp !== $otp) {
                return Helper::jsonErrorResponse('Invalid OTP', 400);
            }
            $token = Str::random(60);
            $user->update([
                'otp' => null,
                'otp_expires_at' => null,
                'reset_password_token' => $token,
                'reset_password_token_expire_at' => Carbon::now()->addHour(),
            ]);
            return response()->json([
                'status' => true,
                'message' => 'OTP verified successfully.',
                'code' => 200,
                'token' => $token,
            ]);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }

    public function ResetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',

        ]);

        try {
            $email = $request->input('email');
            $newPassword = $request->input('password');

            $user = User::where('email', $email)->first();
            if (!$user) {
                return Helper::jsonErrorResponse('User not found', 404);
            }

            if (!empty($user->reset_password_token) && $user->reset_password_token === $request->token && $user->reset_password_token_expire_at >= Carbon::now()) {
                $user->update([
                    'password' => Hash::make($newPassword),
                    'reset_password_token' => null,
                    'reset_password_token_expire_at' => null,
                ]);

                return Helper::jsonResponse(true, 'Password reset successfully.', 200);
            } else {
                return Helper::jsonErrorResponse('Invalid Token', 419);
            }
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }


//password manager
    public function teacherPasswordManager(Request $request): \Illuminate\Http\JsonResponse
    {
        
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = auth('api')->user();

            if (!$user) {
                return Helper::jsonErrorResponse('User not found.', 404);
            }
            
            if (!Hash::check($request->input('old_password'), $user->password)) {
                return Helper::jsonErrorResponse('Invalid old password.', 422);
            }

            $user->password = Hash::make($request->input('new_password'));
            $user->save();
            return Helper::jsonResponse(true, 'Password changed successfully.', 200,$user);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
   
}
