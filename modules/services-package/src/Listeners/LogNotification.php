<?php

namespace Satis2020\ServicePackage\Listeners;

use Illuminate\Support\Facades\Log;
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
    public function handle($event)
    {
        $notification = $event->notification;
        dd($notification);

        /*if (
            $notification->type==AcknowledgmentOfReceipt::class ||
            $notification->type==CommunicateTheSolution::class
        ) {
            $data = $notification->data;
            $institution_id = $data['institution_id'];
            $this->storeProof($data,$institution_id);
        }*/
    }
}
