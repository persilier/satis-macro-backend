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
     * @throws \Illuminate\Http\Client\RequestException
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
     * @throws \Illuminate\Http\Client\RequestException
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
     * @throws \Illuminate\Http\Client\RequestException
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

    /***
     * @param $username
     * @param $password
     * @param $client
     * @param $app
     * @param $id
     * @param $priority
     * @param $to
     * @param $text
     * @return array|mixed
     */
    static public function londoSMSApi($username, $password ,$client, $app, $id, $priority, $to, $text)
    {
        $headers = [
            "Authorization" => "Basic ".base64_encode("$username:$password")
        ];
        $data = [
            '_id' => $id,
            'priority' => $priority,
            'telephone' => $to,
            'message' => $text,
            'source' => [
                'client' => $client,
                'app' => $app
            ]
        ];
        return Http::withHeaders($headers)->post("https://gateway.londo-tech.com/api/v1/send/sms", $data);

    }

}