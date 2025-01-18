<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawCompleteNotification extends Notification
{
    use Queueable;

    private $withdrawalRequest;

    // Pass the withdrawal request to the constructor
    public function __construct($withdrawalRequest)
    {
        $this->withdrawalRequest = $withdrawalRequest;
    }

    // Define which channels the notification will be sent through
    public function via($notifiable): array
    {
        return ['database'];
    }

    // Database notification
    public function toArray($notifiable): array
    {
        return [
            'message' => 'Your withdrawal request has been successfully completed.',
            'withdrawal_id' => $this->withdrawalRequest->id,
            'amount' => $this->withdrawalRequest->amount,
            'user_avatar' => url($this->withdrawalRequest->user->avatar),
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
