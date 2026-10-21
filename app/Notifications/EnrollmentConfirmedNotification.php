<?php

namespace App\Notifications;

use App\Models\Course;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class EnrollmentConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Course $course,
    ) {}

    public static function sendFor(User $user, Course $course): void
    {
        NotificationFacade::route('mail', $user->email)
            ->notify(new self($course));
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $course = $this->course;
        $startUrl = route('learning.resume', $course);
        $supportEmail = (string) config('brightside.support_email', 'support@brightside.test');

        return (new MailMessage)
            ->subject('You are enrolled: '.$course->title)
            ->view('mail.enrollment-confirmed', [
                'course' => $course,
                'startUrl' => $startUrl,
                'supportEmail' => $supportEmail,
            ])
            ->text('mail.enrollment-confirmed-text', [
                'course' => $course,
                'startUrl' => $startUrl,
                'supportEmail' => $supportEmail,
            ]);
    }
}
