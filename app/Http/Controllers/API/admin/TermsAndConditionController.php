<?php

namespace App\Http\Controllers\API\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use App\Models\TermsandCondition;
use App\Models\Privacypolicy;
use Throwable;


class TermsAndConditionController extends Controller
{
    public function getTermsAndConditions(): \Illuminate\Http\JsonResponse
{
    try {

        $user = Auth::user();

        if (!$user) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }

        $termsAndConditions = TermsAndCondition::first();

        if (!$termsAndConditions) {
            return Helper::jsonResponse(false, 'Terms and Conditions not found.', 404, []);
        }
        return Helper::jsonResponse(true, 'Terms and Conditions retrieved successfully.', 200, [
            'terms' => strip_tags($termsAndConditions->terms),
            'conditions' => strip_tags($termsAndConditions->conditions),
        ]);
    } catch (Throwable $th) {
        Log::error($th->getMessage());
        return Helper::jsonResponse(false, 'Something went wrong.', 500);
    }
}
    public function getPrivacyPolicy(): \Illuminate\Http\JsonResponse
{
    try {
        $user = Auth::user();
        if (!$user) {
            return Helper::jsonErrorResponse('User not authenticated.', 401);
        }
        $privacyPolicy = PrivacyPolicy::first();
        if (!$privacyPolicy) {
            return Helper::jsonResponse(false, 'Privacy Policy not found.', 404, []);
        }
        return Helper::jsonResponse(true, 'Privacy Policy retrieved successfully.', 200, [
            'privacy' => strip_tags ($privacyPolicy->privacy),
            'policy' => strip_tags( $privacyPolicy->policy),
        ]);
    } catch (Exception $e) {
        Log::error($e->getMessage());
        return Helper::jsonResponse(false, 'Something went wrong.', 500);
    }
}

}
