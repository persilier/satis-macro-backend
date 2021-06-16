<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class PasswordResetRequest
 * @package Satis2020\UserPackage\Http\Controllers\Auth
 */
class PasswordResetRequest extends Notification implements ShouldQueue
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     *
     * @param $token
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = env('URL_FORGOT_PASSWORD_CLIENT', 'http://127.0.0.1:3000/forgot-password').'/'.$this->token;

        return (new MailMessage)
            ->subject(__('passwords.email_password_reset_request_subject'))
            ->line(__('passwords.email_password_reset_request_line1'))
            ->line(__('passwords.email_password_reset_request_line2'))
            ->action(__('passwords.email_password_reset_request_action'), url($url))
            ->line(__('passwords.email_password_reset_request_line2'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
