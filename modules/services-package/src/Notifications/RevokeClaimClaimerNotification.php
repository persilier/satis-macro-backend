<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Satis2020\ServicePackage\Channels\MessageChannel;

/**
 * Class RevokeClaimClaimerNotification
 * @package Satis2020\ServicePackage\Notifications
 */
class RevokeClaimClaimerNotification extends Notification implements ShouldQueue
{
    use Queueable, \Satis2020\ServicePackage\Traits\Notification;

    public $claim;
    public $event;

    /**
     * Create a new notification instance.
     *
     * @param $claim
     */
    public function __construct($claim)
    {
        $this->claim = $claim;

        $this->event = $this->getNotification('revoke-claim-claimer-notification');

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
        return ($this->claim->response_channel_slug == 'sms' || is_null($this->claim->response_channel_slug))
            ? [MessageChannel::class]
            : ['mail'];
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
            ->subject('Réclamation Révoquée')
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
            'to' => is_null($this->claim->createdBy) ? $this->claim->institutionTargeted->iso_code
                .$notifiable->telephone[0] : $this->claim->createdBy->institution->iso_code.$notifiable->telephone[0],
            'text' => $this->event->text,
            'institutionMessageApi' =>  is_null($this->claim->createdBy) ?
                $this->claim->institutionTargeted->institutionMessageApi :
                $this->claim->createdBy->institution->institutionMessageApi
        ];
    }
}
