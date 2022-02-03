<?php

namespace Satis2020\ServicePackage\Services;


use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Consts\NotificationConsts;
use Satis2020\ServicePackage\MessageApiMethod;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Traits\NotificationProof;

class SendSMService
{
    use Notification,NotificationProof;

    public function send($data,$prove=false)
    {
        try {
            $params = $data['institutionMessageApi']->params;
            $params['to'] = $data['to'];
            $params['text'] = $this->remove_accent($data['text']);

            $messageApi = $data['institutionMessageApi']->messageApi;

            $messageApiParams = [];

            foreach ($messageApi->params as $param) {
                $messageApiParams[] = $params[$param];
            }

            // Send notification to the $notifiable instance...
            $messageSent =  call_user_func_array([MessageApiMethod::class, $messageApi->method], $messageApiParams);

            //save notification proof
            if ($prove && $messageSent){
                $proofData = [
                    "message"=>$params['text'],
                    "channel"=>NotificationConsts::SMS_CHANEL,
                    "sent_at"=>now(),
                    "to"=>$data['notifiable_id'],
                ];
                self::storeProof($proofData,$data['institution_id']);
            }
            return $messageSent;
        } catch (\Exception $exception) {
            Log::debug($exception);
            return $exception->getMessage();

        }
    }
}