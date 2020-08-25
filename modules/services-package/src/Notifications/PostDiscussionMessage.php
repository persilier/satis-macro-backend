<?php

namespace Satis2020\ServicePackage\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Satis2020\ServicePackage\Models\Message;

class PostDiscussionMessage extends Notification implements ShouldQueue
{
    use Queueable, \Satis2020\ServicePackage\Traits\Notification;

    public $discussion;
    public $event;
    public $institution;
    public $claim;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @param $message
     * @param $messages
     */
    public function __construct($message)
    {

        $this->message = $message;

        $this->discussion = $message->discussion;

        $this->claim = $message->discussion->claim;

        $this->event = $this->getNotification('post-discussion-message');

        $postedByIdentity = $this->message->postedBy->identite;
        $postedBy = is_null($postedByIdentity) ? null : "{$postedByIdentity->firstname} {$postedByIdentity->lastname}";

        $this->event->text = str_replace('{posted_by}', $postedBy, $this->event->text);

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
        return ['broadcast'];
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
            //
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
            'discussion' => $this->discussion,
            'message' => $this->message,
            'messages' => Message::with('parent.postedBy.identite', 'files', 'postedBy.identite')
                ->where('discussion_id', $this->discussion->id)
                ->orderByDesc('created_at')
                ->get()
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
