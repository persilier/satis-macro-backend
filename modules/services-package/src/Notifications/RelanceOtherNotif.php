<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RelanceOtherNotif extends Notification
{
    use Queueable;

    protected $message, $pilot;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message, $pilot)
    {
        $this->message = $message;
        $this->pilot = $pilot;
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

        return (new MailMessage)
            ->subject('Relance')
            ->markdown('ServicePackage::mail.relance.notif', [
               "data"=>[ 'pilot' => $this->pilot,
                   'message' => $this->message]
            ]);
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
