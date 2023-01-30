<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Satis2020\ServicePackage\Channels\MessageChannel;

class AddContributorToDiscussion extends Notification implements ShouldQueue
{
    use Queueable, \Satis2020\ServicePackage\Traits\Notification;

    public $discussion;
    public $event;
    public $institution;
    public $claim;

    /**
     * Create a new notification instance.
     *
     * @param $discussion
     */
    public function __construct($discussion)
    {
        $this->discussion = $discussion;

        $this->claim = $discussion->claim;

        $this->event = $this->getNotification('add-contributor-to-discussion');

        $createdByIdentity = $this->discussion->createdBy->identite;
        $createdBy = is_null($createdByIdentity) ? null : "{$createdByIdentity->firstname} {$createdByIdentity->lastname}";

        $this->event->text = str_replace('{created_by}', $createdBy, $this->event->text);

        $this->event->text = str_replace('{discussion_name}', $this->discussion->name, $this->event->text);

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
        return ['database', 'broadcast'];
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
            ->subject('Add Contributor To Discussion')
            ->markdown('ServicePackage::mail.discussion.feedback', [
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
            'claim' => $this->claim,
            'discussion' => $this->discussion
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
            'claim' => $this->claim,
            'discussion' => $this->discussion
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
            //
        ];
    }
}
