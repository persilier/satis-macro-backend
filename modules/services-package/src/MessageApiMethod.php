<?php


namespace Satis2020\ServicePackage;


use Illuminate\Support\Facades\Http;

class MessageApiMethod
{
    /**
     * oceanicsms.com Message Api
     *
     * @param $user
     * @param $password
     * @param $from
     * @param $to
     * @param $text
     * @param $api
     * @return mixed
     */
    public function toOceanicsms($user, $password, $from, $to, $text, $api)
    {

        return $response = Http::asForm()->post('http://oceanicsms.com/api/http/sendmsg.php', [
            'user' => $user,
            'password' => $password,
            'from' => $from,
            'to' => $to,
            'text' => $text,
            'api' => $api
        ]);

    }
}