<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WithdrawRequestNotification extends Notification
{
    use Queueable;

    private $withdrawRequest;

    /**
     * Create a new notification instance.
     *
     * @param $withdrawRequest
     */
    public function __construct($withdrawRequest)
    {
        $this->withdrawRequest = $withdrawRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'message' => 'Your withdrawal request for ' . number_format($this->withdrawRequest->amount, 2) . ' has been submitted.',
            'withdraw_request_id' => $this->withdrawRequest->id,
            'amount' => $this->withdrawRequest->amount,
            'status' => $this->withdrawRequest->status,
        ];
    }
}
