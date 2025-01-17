<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use JetBrains\PhpStorm\NoReturn;

class EnrollNotification extends Notification
{
    use Queueable;

    private $course;

    public function __construct($course)
    {
        $this->course = $course;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {

        return [
            'message' => 'You have successfully enrolled in the course: ' . $this->course->name,
            'cover_image' => $this->course->cover_image,
            'course_id' => $this->course->id,
        ];
    }

}
