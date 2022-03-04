<?php

namespace Satis2020\ServicePackage\Listeners;

use Illuminate\Notifications\Events\NotificationSent;

use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\Notifications\AcknowledgmentOfReceipt;
use Satis2020\ServicePackage\Notifications\CommunicateTheSolution;
use Satis2020\ServicePackage\Traits\NotificationProof;

class LogNotification
{
    use NotificationProof;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(NotificationSent $event)
    {
        $notification = $event->notification;

        if
        (
            (get_class($notification) == AcknowledgmentOfReceipt::class ||

                get_class($notification) == CommunicateTheSolution::class)
            && $event->channel == "mail"
        )
        {
            $text = $notification->event->text;
            $to = $event->notifiable->id;
            $institution = $notification->institution;

            $data = [
                "message"=>$text,
                "channel"=>NotificationConsts::EMAIL_CHANNEL,
                "sent_at"=>now(),
                "to"=>$to
            ];
            $this->storeProof($data,$institution->id);

        }
    }

}
