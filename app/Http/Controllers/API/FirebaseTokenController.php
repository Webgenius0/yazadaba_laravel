<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\FirebaseToken;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;

class FirebaseTokenController extends Controller
{
    public function test()
    {
        $user = User::find(2);
        if ($user->firebaseTokens) {
            $notifyData = [
                'title' => "test title",
                'body'  => "test body",
            ];
            foreach ($user->firebaseTokens as $firebaseToken) {
                Helper::sendNotifyMobile($firebaseToken->token, $notifyData);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Token saved successfully',
            'data' => $user->firebaseTokens,
            'code' => 200,
        ], 200);
    }

    /**
     * News Serve For Frontend
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'device_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }
        //first delete existing token
        $firebase = FirebaseToken::where('user_id', auth('api')->user()->id)->where('device_id', $request->device_id);
        if ($firebase) {
            $firebase->delete();
        }
        try {
            $data = new FirebaseToken();
            $data->user_id = auth('api')->user()->id;
            $data->token = $request->token;
            $data->device_id = $request->device_id;
            $data->status = "active";
            $data->save();

            return response()->json([
                'status' => true,
                'message' => 'Token saved successfully',
                'data' => $data,
                'code' => 200,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'No records found',
                'code' => 418,
                'data' => [],
            ], 418);
        }
    }

    /**
     * Get Single Record
     * @param Request $request
     * @return JsonResponse
     */
    public function getToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }
        $user_id = auth('api')->user()->id;
        $device_id = $request->device_id;
        $data = FirebaseToken::where('user_id', $user_id)->where('device_id', $device_id)->first();
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'No records found',
                'code' => 404,
                'data' => [],
            ], 404);
        }
        return response()->json([
            'status' => true,
            'message' => 'Token fetched successfully',
            'data' => $data,
            'code' => 200,
        ], 200);
    }

    /**
     * Delete Token Single Record
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $user = FirebaseToken::where('user_id', auth('api')->user()->id)->where('device_id', $request->device_id);
        if ($user) {
            $user->delete();
            return response()->json([
                'status' => true,
                'message' => 'Token deleted successfully',
                'code' => 200,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No records found',
                'code' => 404,
            ], 404);
        }
    }
}
