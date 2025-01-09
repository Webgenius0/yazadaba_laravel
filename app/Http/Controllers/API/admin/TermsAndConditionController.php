<?php

namespace App\Http\Controllers\API\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use App\Models\TermsandCondition;


class TermsAndConditionController extends Controller
{
    public function updateOrCreate(Request $request): \Illuminate\Http\JsonResponse
    {

        //dd($request->all());
        try {
        $user=Auth::user();
           // dd($user);
        if(!$user)
        {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        if ($user->role != "admin") {
            return Helper::jsonResponse(false, 'Access denied. User is not an admin.', 403, []);
        }

        $request->validate([
            'terms'=>'nullable|string',
            'conditions'=>'nullable|string',
        ]);

        $termsandconditon= TermsandCondition::first();

        if(!$termsandconditon)
        {
            $termsandconditon=TermsandCondition::Create([
                'terms'=>$request->terms,

                'conditions'=>$request->conditions
            ]);
        }
        else{

            $termsandconditon->update([
                'terms'=>$request->terms,
                'conditions'=>$request->conditions
            ]);
        }

        return Helper::jsonResponse(true, 'Terms and Conditions updated successfully.', 200, [
            'terms' => $termsandconditon->terms,
            'conditions' => $termsandconditon->conditions,
        ]);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return Helper::jsonResponse(false, 'Something went wrong.', 500);
        }
    }
}
