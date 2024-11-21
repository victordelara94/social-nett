<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoginAttemptFailed extends Notification
{
    use Queueable;
    protected $email;
    protected $reactivationCode;
    /**
     * Create a new notification instance.
     */
    public function __construct($email, $reactivationCode)
    {
        $this->email = $email;
        $this->reactivationCode = $reactivationCode;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $reactivationUrl = url('/api/users/reactivate-account?code=' . urldecode($this->reactivationCode) . '&email=' . urlencode($this->email));

        return (new MailMessage)
            ->subject('Failed login attempts and Account Lock')
            ->greeting('Hello')
            ->line('There are multiple failed login attempts to your account using the email ' . $this->email)
            ->line('Your account has been locked due to too many failed attempts.')
            ->action('Reactivate your account', $reactivationUrl)
            ->line('If this was not you, we recommend changing your password.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
