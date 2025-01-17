<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class WithdrawRequestRejected extends Notification
{
    use Queueable;

    private $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Withdrawal Request Rejected')
            ->line('Your withdrawal request has been rejected.')
            ->line('Reason: ' . $this->reason)
            ->line('If you have any questions, please contact support.')
            ->salutation('Thank you');
    }
    public function toArray($notifiable): array
    {
        return [
            'message' => 'Your withdrawal request has been rejected.',
            'reason' => $this->reason,

        ];
    }
}
