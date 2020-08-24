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
    static public function toOceanicsms($user, $password, $from, $to, $text, $api)
    {

            return $response = Http::asForm()->post('http://oceanicsms.com/api/http/sendmsg.php', [
                'user' => $user,
                'password' => $password,
                'from' => $from,
                'to' => $to,
                'text' => $text,
                'api' => $api
            ])->throw()->json();
    
    }

    /**
     * oceanicsms.com Message Api
     *
     * @param $password
     * @param $from
     * @param $to
     * @param $text
     * @return mixed
     */
    static public function toMessageApi2($password, $from, $to, $text)
    {

            return $response = Http::asForm()->post('http://oceanicsms.com/api/http/sendmsg.php', [
                'username' => 'satisuimcec',
                'password' => $password,
                'from' => $from,
                'to' => $to,
                'text' => $text,
                'api' => "14265"
            ])->throw()->json();        

    }

    /**
     * oceanicsms.com Message Api
     *
     * @param $username
     * @param $senderId
     * @param $to
     * @param $text
     * @param $apiId
     * @return mixed
     */
    static public function toMessageApi3($username, $senderId, $to, $text, $apiId)
    {

            return $response = Http::asForm()->post('http://oceanicsms.com/api/http/sendmsg.php', [
                'username' => $username,
                'password' => 'SatisUimcec',
                'from' => $senderId,
                'to' => $to,
                'text' => $text,
                'api' => $apiId
            ])->throw()->json();
        

    }

}