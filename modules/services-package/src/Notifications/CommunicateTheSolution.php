<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Satis2020\ServicePackage\Channels\MessageChannel;
use Satis2020\ServicePackage\Traits\NotificationProof;
use Satis2020\ServicePackage\Consts\NotificationConsts;

class CommunicateTheSolution extends Notification implements ShouldQueue
{
    use Queueable, \Satis2020\ServicePackage\Traits\Notification, NotificationProof;

    public $claim;
    public $files;
    public $event;
    public $institution;

    /**
     * Create a new notification instance.
     *
     * @param $claim
     */
    public function __construct($claim, $files = null)
    {
        $this->claim = $claim;

        $this->files = $files;

        $this->event = $this->getNotification('communicate-the-solution');

        $this->event->text = str_replace('{solution_communicated}', $this->claim->activeTreatment->solution_communicated, $this->event->text);

        $this->event->text = str_replace('{claim_reference}', $this->claim->reference, $this->event->text);

        $this->event->text = str_replace('{claim_object}', $this->claim->claimObject->name, $this->event->text);

        $this->institution = $this->claim->activeTreatment->responsibleStaff->institution;
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
        $ref = formatClaimRef($this->claim->reference);

        $email =  (new MailMessage)
            ->subject($ref . " Réclamation traitée")
            ->markdown('ServicePackage::mail.claim.feedback', [
                'text' => $this->event->text,
                'name' => "{$notifiable->firstname} {$notifiable->lastname}",
            ]);
         
        if (count($this->files) > 0) {
            foreach ($this->files as $file) {
                Log::info(public_path($file->url));
                $email->attach(public_path($file->url), [
                    'as' => $file->title,
                ]);
            }
        }

        return $email;
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
            'to' => $this->institution->iso_code . $notifiable->telephone[0],
            'text' => $this->event->text,
            'institutionMessageApi' => $this->getStaffInstitutionMessageApi($this->institution),
            'institution_id' => $this->institution->id,
            'notifiable_id' => $notifiable->id
        ];
    }
}
