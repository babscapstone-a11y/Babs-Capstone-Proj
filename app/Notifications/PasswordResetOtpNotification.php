<?php

namespace App\Notifications;

use App\Models\PasswordResetOtp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification
{
    use Queueable;

    /**
     * @param  string|null  $enterCodeUrl  Link to the "enter the code" page, for codes that arrive
     *                                     after the person has left it (staff, on admin approval).
     */
    public function __construct(
        public readonly string $otp,
        public readonly int $expiresMinutes = PasswordResetOtp::EXPIRES_MINUTES,
        public readonly ?string $enterCodeUrl = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Your Password Reset Code')
            ->greeting('Hello!')
            ->line($this->enterCodeUrl
                ? 'Your password reset request was approved. Use the code below to reset your BAB\'S RESTO account password.'
                : 'Use the code below to reset your BAB\'S RESTO account password.')
            ->line(new \Illuminate\Support\HtmlString(
                '<div style="font-size:28px;font-weight:700;letter-spacing:6px;text-align:center;margin:16px 0">'.$this->otp.'</div>'
            ));

        if ($this->enterCodeUrl) {
            $mail->action('Enter Code', $this->enterCodeUrl)
                ->line('You can also open Forgot Password on the login page and enter your email address to type in this code.');
        }

        return $mail
            ->line("This code expires in {$this->expiresMinutes} minutes.")
            ->line('If you did not request a password reset, no further action is required.');
    }
}
