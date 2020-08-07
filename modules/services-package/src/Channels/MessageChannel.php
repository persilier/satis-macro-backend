<?php

namespace Satis2020\ServicePackage\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Validator;
use Satis2020\ServicePackage\MessageApiMethod;

class MessageChannel
{
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
            $params['text'] = $data['text'];

            $messageApi = $data['institutionMessageApi']->messageApi;

            $messageApiParams = [];

            foreach ($messageApi->params as $param) {
                $messageApiParams[] = $params[$param];
            }

            // Send notification to the $notifiable instance...
            return call_user_func_array([MessageApiMethod::class, $messageApi->method], $messageApiParams);
        }catch (\Exception $exception){
            return $exception->getMessage();
        }
    }
}