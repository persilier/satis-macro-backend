<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Satis2020\ServicePackage\Channels\MessageChannel;
use Satis2020\ServicePackage\Traits\ClaimIncomingByEmail;

/**
 * Class AcknowledgmentOfReceipt
 * @package Satis2020\ServicePackage\Notifications
 */
class AcknowledgmentOfReceipt extends Notification implements ShouldQueue
{
    use Queueable, \Satis2020\ServicePackage\Traits\Notification, ClaimIncomingByEmail;

    public $claim;
    public $event;
    public $institution;

    /**
     * Create a new notification instance.
     *
     * @param $claim
     */
    public function __construct($claim)
    {
        $this->claim = $claim;

        $event = $claim->claimObject && $claim->claimObject!=null?
            'acknowledgment-of-receipt':'acknowledgment-of-receipt-incoming';
        $this->event = $this->getNotification($event);

        if ($claim->claimObject && $claim->claimObject!=null){
            $this->event->text = str_replace('{claim_object}', $this->claim->claimObject->name, $this->event->text);
            $this->event->text = str_replace('{day_replay}', $this->claim->created_at->addWeekdays($this->claim->claimObject->time_limit), $this->event->text);
        }else{
            $this->event->text = str_replace('{claim_object}', "--", $this->event->text);
            $this->event->text = str_replace('{day_replay}', "--", $this->event->text);
        }

        $this->event->text = str_replace('{claim_reference}', $this->claim->reference, $this->event->text);
        $this->institution = is_null($this->claim->createdBy) ? $this->claim->institutionTargeted
            :  $this->claim->createdBy->institution;
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
            ? [MessageChannel::class,'database']
            : ['mail','database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $ref = formatClaimRef($this->claim->reference);

        return (new MailMessage)
            ->replyTo($this->getConfiguration($this->claim->institution_targeted_id)->email,env('MAIL_FROM_NAME'))
            ->replyTo(['email' => $this->getConfiguration($this->claim->institution_targeted_id)->email])
            ->subject("$ref Accusé de reception")
            ->markdown('ServicePackage::mail.claim.feedback',
                [
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
                .$notifiable->telephone[0] :  $this->claim->createdBy->institution->iso_code .$notifiable->telephone[0],
            'text' => $this->event->text,
            'institutionMessageApi' => is_null($this->claim->createdBy) ? $this->claim->institutionTargeted->institutionMessageApi :
                $this->claim->createdBy->institution->institutionMessageApi,
            'institution_id'=> is_null($this->claim->createdBy) ? $this->claim->institutionTargeted->id
                :  $this->claim->createdBy->institution->id,

            'notifiable_id'=>$notifiable->id
        ];
    }

}
