<?php

namespace App\Http\Controllers\API\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = auth()->user();
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->get();
            $formattedNotifications = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'message' => $notification->data['message'],
                    'withdraw_request_id' => $notification->data['withdraw_request_id'],
                    'amount' => $notification->data['amount'],
                    'user_name' => $notification->data['user_name'],
                    'user_avatar' => $notification->data['user_avatar'],
                    'status' => $notification->data['status'],
                    'created_at' => $notification->created_at->toDateTimeString(),
                ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Notifications retrieved successfully',
            'notifications' => $formattedNotifications,
        ],200);
    }
}
