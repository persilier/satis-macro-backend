<?php

namespace Satis2020\ServicePackage\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\MessageApiMethod;
use Satis2020\ServicePackage\Traits\NotificationProof;

class MessageChannel
{

    use \Satis2020\ServicePackage\Traits\Notification,NotificationProof;

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

        try {
            $params = $data['institutionMessageApi']->params;
            $params['to'] = $data['to'];
            $params['text'] = $this->remove_accent($data['text']);

            $messageApi = $data['institutionMessageApi']->messageApi;

            $messageApiParams = [];

            foreach ($messageApi->params as $param) {
                $messageApiParams[] = $params[$param];
            }
            $messageApiParams['institution_id'] = $data['institution_id'];

            // Send notification to the $notifiable instance...
            $messageSent =  call_user_func_array([MessageApiMethod::class, $messageApi->method], $messageApiParams);

            //save notification proof
            if ($messageSent){
                    $proofData = [
                        "message"=>$params['text'],
                        "channel"=>NotificationConsts::SMS_CHANEL,
                        "sent_at"=>now(),
                        "to"=>$params['to'],
                    ];
                    self::storeProof($proofData,$data['institution_id']);
            }

            return $messageSent;
        }catch (\Exception $exception){
            Log::debug($exception);
            return $exception->getMessage();
        }
    }

}