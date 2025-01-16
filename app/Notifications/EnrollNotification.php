<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

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
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Course Enrollment Successful')
            ->line('You have successfully enrolled in the course: ' . $this->course->name)
            ->line('Start learning and achieve your goals!')
            ->action('View Course', url('/courses/' . $this->course->id))
            ->salutation('Thank you for choosing us!');
    }

    public function toArray($notifiable): array
    {
        return [
            'message' => 'You have successfully enrolled in the course: ' . $this->course->name,
            'course_id' => $this->course->id,
        ];
    }
}
