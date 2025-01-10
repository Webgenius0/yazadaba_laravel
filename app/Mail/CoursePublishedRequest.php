<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CoursePublishedRequest extends Mailable
{
    public $publishRequest;
    public $courseName;
    public $userName;
    public $userEmail;
    public $courseDescription;
    public $coursePrice;
    public $statusMessage;

    public function __construct($publishRequest)
    {
        $this->publishRequest = $publishRequest;
        $this->courseName = $publishRequest->course->name;
        $this->userName = $publishRequest->user->name;
        $this->userEmail = $publishRequest->user->email;
        $this->courseDescription = $publishRequest->course->description;
        $this->coursePrice = $publishRequest->course->price;
        $this->statusMessage = $this->getStatusMessage($publishRequest->status);
    }

    /**
     * Get the appropriate status message based on course status.
     *
     * @param string $status
     * @return string
     */
    public function getStatusMessage($status)
    {
        if ($status == 'pending') {
            return 'Your course is pending approval.';
        } else {
            return 'Your course has been published.';
        }
    }

    public function build()
    {
        return $this->subject('Publish Request Update')
            ->view('mail.course_published_request');
    }
}
