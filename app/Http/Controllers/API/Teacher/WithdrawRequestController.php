<?php

namespace App\Http\Controllers\API\Teacher;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\WithdrawRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WithdrawRequestController extends Controller
{

    public function index(Request $request){
        $request->validate([
            'request_amount' => 'required|numeric',
            'bank_info' =>'required'
        ]);
        try {
            $user = auth()->user();
            $withdrawRequest = new WithdrawRequest();
            $withdrawRequest->user_id = $user->id;
            $withdrawRequest->request_amount = $request->request_amount;
            $withdrawRequest->bank_info = $request->bank_info;
            $withdrawRequest->save();
            return Helper::jsonResponse(true, "Withdraw request successfully created!",200, $withdrawRequest);
        }catch (Exception $exception){
            Log::error($exception);
            return Helper::jsonResponse(false, $exception->getMessage(), 500, $exception);
        }
    }
}
