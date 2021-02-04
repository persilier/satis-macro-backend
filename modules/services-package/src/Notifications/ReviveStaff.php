<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Satis2020\ServicePackage\Channels\MessageChannel;
use Satis2020\ServicePackage\Models\Claim;

class ReviveStaff extends Notification implements ShouldQueue
{
    use Queueable, \Satis2020\ServicePackage\Traits\Notification;
    /**
     * @var Claim
     */
    private $claim;
    /**
     * @var string
     */
    private $text;

    /**
     * Create a new notification instance.
     *
     * @param Claim $claim
     * @param string $text
     */
    public function __construct(Claim $claim, string $text)
    {
        //
        $this->claim = $claim;
        $this->text = $text;
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
            ->subject('Relance')
            ->markdown('ServicePackage::mail.claim.feedback', [
                'text' => $this->text,
                'name' => "{$notifiable->firstname} {$notifiable->lastname}",
                'claim' => $this->claim
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
            'text' => $this->text,
            'name' => "{$notifiable->firstname} {$notifiable->lastname}",
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
            'text' => $this->text,
            'name' => "{$notifiable->firstname} {$notifiable->lastname}",
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
            'text' => $this->text,
            'institutionMessageApi' => $this->getStaffInstitutionMessageApi($notifiable->staff->institution)
        ];
    }
}
