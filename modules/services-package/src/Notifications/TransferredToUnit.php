<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Satis2020\ServicePackage\Channels\MessageChannel;

class TransferredToUnit extends Notification implements ShouldQueue
{
    use Queueable, \Satis2020\ServicePackage\Traits\Notification;

    public $claim;
    public $event;
    public $isEscalation;

    /**
     * Create a new notification instance.
     *
     * @param $claim
     */
    public function __construct($claim, $isEscalation = false)
    {
        $this->isEscalation = $isEscalation;

        $this->claim = $claim;

        $this->event = $isEscalation == true ? $this->getNotification('transferred-to-unit-escalation') : $this->getNotification('transferred-to-unit');

        $this->event->text = str_replace('{claim_reference}', $this->claim->reference, $this->event->text);

        $this->event->text = str_replace('{claim_object}', $this->claim->claimObject->name, $this->event->text);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $preferredChannels = $this->getFeedBackChannels($notifiable->staff);
        return collect([$preferredChannels, ['database', 'broadcast']])->collapse()->all();
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
            ->subject($this->isEscalation == true ? 'Réclamation transférée à un comité de traitement' : 'Réclamation transférée à une unité de traitement')
            ->markdown('ServicePackage::mail.claim.feedback', [
                'text' => $this->event->text,
                'name' => "{$notifiable->firstname} {$notifiable->lastname}"
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'text' => $this->event->text,
            'claim' => $this->claim
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     *
     * @param mixed $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'text' => $this->event->text,
            'claim' => $this->claim
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

    /**
     * Get the message representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toMessage($notifiable)
    {
        return [
            'to' => $notifiable->staff->institution->iso_code . $notifiable->telephone[0],
            'text' => $this->event->text,
            'institutionMessageApi' =>  $this->getStaffInstitutionMessageApi($notifiable->staff->institution)
        ];
    }
}
