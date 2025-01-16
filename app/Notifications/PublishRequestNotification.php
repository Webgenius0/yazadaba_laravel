<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PublishRequestNotification extends Notification
{
    use Queueable;

    protected $publishRequest;

    /**
     * Create a new notification instance.
     *
     * @param $publishRequest
     */
    public function __construct($publishRequest)
    {
        $this->publishRequest = $publishRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Course Publish Request')
            ->greeting('Hello Admin,')
            ->line('A user has submitted a request to toggle the status of a course.')
            ->line("Course ID: {$this->publishRequest->course_id}")
            ->line("Requested by User ID: {$this->publishRequest->user_id}")
            ->action('Review Request', url('/admin/requests'))
            ->line('Thank you for your attention.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase(mixed $notifiable): array
    {
        return [
            'request_id' => $this->publishRequest->id,
            'user_id' => $this->publishRequest->user_id,
            'course_id' => $this->publishRequest->course_id,
            'status' => $this->publishRequest->status,
            'publish_status' => $this->publishRequest->publish_status,
            'message' => "A request to toggle the status of course ID {$this->publishRequest->course_id} has been submitted.",
        ];
    }
}
