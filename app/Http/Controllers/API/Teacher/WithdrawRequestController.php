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

        // Create the withdrawal request
        $withdrawalRequest = WithdrawRequest::create([
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'bank_info' => $request->bank_info,
            'status' => 'pending',
        ]);

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request created successfully.',
            'data' => $withdrawalRequest
        ]);
    }
}
