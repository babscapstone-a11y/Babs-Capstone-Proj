<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly string $otp)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Reset Code')
            ->greeting('Hello!')
            ->line('Use the code below to reset your BAB\'S RESTO account password.')
            ->line(new \Illuminate\Support\HtmlString(
                '<div style="font-size:28px;font-weight:700;letter-spacing:6px;text-align:center;margin:16px 0">'.$this->otp.'</div>'
            ))
            ->line('This code expires in 10 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
