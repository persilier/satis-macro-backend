<?php

namespace Satis2020\ServicePackage\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Satis2020\ServicePackage\MessageApiMethod;

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

        try {
            $params = $data['institutionMessageApi']->params;
            $params['to'] = $data['to'];
            $params['text'] = $this->remove_accent($data['text']);

            $messageApi = $data['institutionMessageApi']->messageApi;

            $messageApiParams = [];

            foreach ($messageApi->params as $param) {
                $messageApiParams[] = $params[$param];
            }

            Log::info("send sms method ".$messageApi->method);
            // Send notification to the $notifiable instance...
            $response = call_user_func_array([MessageApiMethod::class, $messageApi->method], $messageApiParams);
           Log::debug($response);
            return $response;
        }catch (\Exception $exception){
            Log::debug($exception);
            return $exception->getMessage();
        }
    }

}