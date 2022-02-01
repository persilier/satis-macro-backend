<?php

namespace Satis2020\ServicePackage\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Satis2020\ServicePackage\MessageApiMethod;
use Satis2020\ServicePackage\Notifications\AcknowledgmentOfReceipt;
use Satis2020\ServicePackage\Notifications\CommunicateTheSolution;
use Satis2020\ServicePackage\Services\SendSMService;

class MessageChannel
{

    use \Satis2020\ServicePackage\Traits\Notification;

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param \Illuminate\Notifications\Notification $notification
     * @return string
     */
    public function send($notifiable, Notification $notification)
    {
        $data = $notification->toMessage($notifiable);

        $prove = get_class($notification) == AcknowledgmentOfReceipt::class ||
            get_class($notification) == CommunicateTheSolution::class;

        return (new SendSMService())->send($data,$prove);
    }

}