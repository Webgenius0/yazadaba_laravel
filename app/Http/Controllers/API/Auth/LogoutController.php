<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Exception;


class LogoutController extends Controller
{
    public function logout(): \Illuminate\Http\JsonResponse
    {
        try {
            if (Auth::check('api')) {
                Auth::logout('api');
                return Helper::jsonResponse(true, 'Logged out successfully. Token revoked.', 200);
            }

            return Helper::jsonErrorResponse( 'User not authenticated', 401);
        } catch (Exception $e) {
            return Helper::jsonErrorResponse($e->getMessage(), 500);
        }
    }
}
