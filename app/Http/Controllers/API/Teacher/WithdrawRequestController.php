<?php

namespace App\Http\Controllers\API\Teacher;

use Exception;
use App\Models\Course;
use App\Helpers\Helper;
use App\Models\CourseEnroll;
use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Notifications\WithdrawRequestNotification;

class WithdrawRequestController extends Controller
{
    public function withdrawRequest(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'bank_info' => 'required|string|max:255',
        ]);

        $user = auth()->user();

        // Check if there's an existing pending withdrawal request
        $existingPendingRequest = WithdrawRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($existingPendingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending withdrawal request. Please wait until the current request is processed.',
            ], 400);
        }

        // Get all courses created by the authenticated user
        $courses = Course::where('user_id', $user->id)->where('status','active')->pluck('id');
        // Calculate total enrolled amount for the user's courses
        $totalEnrolledAmount = CourseEnroll::whereIn('course_id', $courses)->sum('amount');

        // Calculate total withdrawn amount for the user
        $totalWithdrawn = WithdrawRequest::where('user_id', $user->id)
            ->where('status', 'complete')
            ->sum('amount');

        // Calculate available balance
        $availableBalance = $totalEnrolledAmount - $totalWithdrawn;

        // Calculate maximum withdrawable amount (75% of the available balance)
        $maxWithdrawable = $availableBalance * 0.75;

        // Check if the requested amount is within the withdrawable limit
        if ($request->amount > $maxWithdrawable) {
            return response()->json([
                'success' => false,
                'message' => 'You can only withdraw up to 75% of your available balance.',
                'wallet_balance' => $availableBalance,
                'max_withdrawable' => $maxWithdrawable,
            ], 400);
        }

        // Calculate the remaining balance after the withdrawal
        $remainingBalance = $availableBalance - $request->amount;

        // Check if the request already exists and if it was already completed or rejected
        $withdrawalRequest = WithdrawRequest::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'bank_info' => $request->bank_info,
            'remaining_balance' => $remainingBalance,
            'status' => 'pending',
        ]);

        // Handle the case when an admin updates the request later (pending, rejected)
        // Ensure that the original remaining balance is maintained.
        if ($request->has('status') && in_array($request->status, ['pending', 'rejected'])) {
            // In case the admin wants to update status (pending or rejected)
            $withdrawalRequest->status = $request->status;
            // Ensure the remaining_balance stays the same as initially calculated
            $withdrawalRequest->remaining_balance = $remainingBalance;
        }

        // Save the request (if any status update was made)
        $withdrawalRequest->save();
        // Notify the user about the withdrawal request
        $user->notify(new WithdrawRequestNotification($withdrawalRequest));
        // Return a success response with withdrawal request details

        if ($user->firebaseTokens) {
            $notifyData = [
                'title' => 'Withdrawal Request Submitted',
                'body' => "Your withdrawal request of ৳{$request->amount} has been submitted successfully.",
            ];
            foreach ($user->firebaseTokens as $firebaseToken) {
                Helper::sendNotifyMobile($firebaseToken->token, $notifyData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request created successfully.',
            'data' => [
                'withdraw_request_id' => $withdrawalRequest->id,
                'amount' => $withdrawalRequest->amount,
                'bank_info' => $withdrawalRequest->bank_info,
                'status' => $withdrawalRequest->status,
                'created_at' => $withdrawalRequest->created_at->toDateTimeString(),
                'updated_at' => $withdrawalRequest->updated_at->toDateTimeString(),
                'wallet_balance' => $withdrawalRequest->remaining_balance,
            ],
        ]);
    }

    public function myWallet(Request $request)
    {
        try {
            // Get user ID
            $userId = auth()->user()->id;

            // Get the course IDs for the active courses of the user
            $courses = Course::where('user_id', $userId)
                ->where('status', 'active')
                ->pluck('id');

            // Get the completed course enrollments and their total amount (myWallet balance)
            $myWallet = CourseEnroll::whereIn('course_id', $courses)
                ->where('status', 'completed')
                ->sum('amount');

            // Get the latest withdrawal request for the user (assuming there's only one withdrawal request per user)
            $withdrawalRequest = WithdrawRequest::where('user_id', $userId)
                ->latest()
                ->first();

            // Check if the remaining_balance exists and is greater than 0, otherwise fall back to $myWallet
            if ($withdrawalRequest && $withdrawalRequest->remaining_balance > 0) {
                $remainingBalance = $withdrawalRequest->remaining_balance;
            } else {
                $remainingBalance = $myWallet;
            }
            $remainingBalance = floatval(number_format($remainingBalance, 2, '.', ''));
            $data = [
                'my_wallet' => $remainingBalance,
            ];
           return Helper::jsonResponse(true,'My Wallet Data Fetch Successfully',200,$data);
        } catch (Exception $e) {
            // Log error and return response
            Log::error($e->getMessage());
            return Helper::jsonResponse('false', 'Something went wrong, please try again.', 500);
        }
    }


}

