<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class EmailVerificationOtpNotification extends Notification
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
            ->subject('Verify Your Email Address')
            ->greeting('Welcome to BAB\'S RESTO!')
            ->line('Use the code below to verify your email address and activate your account.')
            ->line(new HtmlString(
                '<div style="font-size:28px;font-weight:700;letter-spacing:6px;text-align:center;margin:16px 0">'.$this->otp.'</div>'
            ))
            ->line('This code expires in 10 minutes.')
            ->line('If you did not create an account, no further action is required.');
    }
}
