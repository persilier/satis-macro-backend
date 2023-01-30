<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReassignmentPilotNotify extends Notification
{
    use Queueable;

    public $claim_reference, $lead, $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($claim_reference, $lead, $message)
    {
        $this->claim_reference = $claim_reference;
        $this->lead = $lead;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Réaffectation d'une réclamation")
            ->markdown('ServicePackage::mail.reassignment.pilot', [
                "data" => [
                    'claim_reference' => $this->claim_reference,
                    'pilot' => $this->lead,
                    'message' => $this->message]
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
