<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Satis2020\ServicePackage\Channels\MessageChannel;
use Satis2020\ServicePackage\Traits\ClaimIncomingByEmail;
use Satis2020\ServicePackage\Models\EmailClaimConfiguration;
use Satis2020\ServicePackage\Traits\Notification as NotificationTrait;

/**
 * Class AcknowledgmentOfReceipt
 * @package Satis2020\ServicePackage\Notifications
 */
class AcknowledgmentOfReceipt extends Notification implements ShouldQueue
{
    use Queueable, NotificationTrait;

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
        //acknowledgment-of-receipt-incoming
        $event = ($claim && $claim->claimObject) ? 'acknowledgment-of-receipt' : 'acknowledgment-of-receipt-incoming';
        $this->event = $this->getNotification($event);

        if ($claim && $claim->claimObject) {
            $this->event->text = str_replace('{claim_object}', $this->claim->claimObject->name, $this->event->text);
            $this->event->text = str_replace('{day_replay}', $this->claim->created_at->addWeekdays($this->claim->claimObject->time_limit), $this->event->text);
        
        } else {
            $this->event->text = str_replace('{claim_object}', "--", $this->event->text);
            $this->event->text = str_replace('{day_replay}', "--", $this->event->text);
        } 

        /* if ($claim->claimObject && $claim->claimObject != null) {
            $this->event->text = str_replace('{claim_object}', $this->claim->claimObject->name, $this->event->text);
            $this->event->text = str_replace('{day_replay}', $this->claim->created_at->addWeekdays($this->claim->claimObject->time_limit), $this->event->text);
        } else {
            $this->event->text = str_replace('{claim_object}', "--", $this->event->text);
            $this->event->text = str_replace('{day_replay}', "--", $this->event->text);
        } */
        //$this->event->text = str_replace('{claim_reference}', $this->claim->reference, $this->event->text);

        
        $this->institution = is_null($this->claim->createdBy) ? $this->claim->institutionTargeted
            : $this->claim->createdBy->institution;
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
            ? [MessageChannel::class, 'database']
            : ['mail', 'database'];
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

        $configurationEmail = EmailClaimConfiguration::query()
                        ->where('institution_id', $this->claim->institution_targeted_id)
                        ->first();

        return (new MailMessage)
            ->replyTo($configurationEmail->email, Config::get('mail.from.name'))
            ->replyTo(['email' => $configurationEmail->email])
            ->subject("$ref Accusé de reception")
            ->markdown(
                'ServicePackage::mail.claim.feedback',
                [

                    'text' => $this->event->text,
                    'name' => "{$notifiable->firstname} {$notifiable->lastname}"
                ]
            );
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
                . $notifiable->telephone[0] : $this->claim->createdBy->institution->iso_code . $notifiable->telephone[0],
            'text' => $this->event->text,
            'institutionMessageApi' => is_null($this->claim->createdBy) ? $this->claim->institutionTargeted->institutionMessageApi :
                $this->claim->createdBy->institution->institutionMessageApi,
            'institution_id' => is_null($this->claim->createdBy) ? $this->claim->institutionTargeted->id
                : $this->claim->createdBy->institution->id,

            'notifiable_id' => $notifiable->id
        ];
    }
}
