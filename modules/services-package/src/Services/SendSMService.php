<?php

namespace Satis2020\ServicePackage\Services;


use Satis2020\ServicePackage\MessageApiMethod;

class SendSMService
{
    use \Satis2020\ServicePackage\Traits\Notification;

    public function send($data)
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
            return call_user_func_array([MessageApiMethod::class, $messageApi->method], $messageApiParams);

        } catch (\Exception $exception) {

            return $exception->getMessage();

        }
    }
}