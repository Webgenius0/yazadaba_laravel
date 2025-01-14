<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnroll;
use App\Models\WithdrawRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $courses = Course::where('user_id', $user->id)->pluck('id');
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
        // Create the withdrawal request
        $withdrawalRequest = WithdrawRequest::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'bank_info' => $request->bank_info,
            'remaining_balance' => $remainingBalance,
            'status' => 'pending',
        ]);
        // Return a success response
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
                'wallet_balance' => $remainingBalance,
            ],
        ]);
    }
}
