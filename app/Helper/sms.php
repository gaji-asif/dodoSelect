<?php

namespace App\SMS_ClASS;

class SMS
{
    private $api;

    private $token;

    public function __construct($apiKey = '', $apiSecretKey = '', $options = [])
    {
        $this->token = base64_encode("$apiKey:$apiSecretKey");
        $this->api = array_key_exists('api', $options) ? $options['api'] : 'https://api-v2.thaibulksms.com';
    }

    public function sendSMS($body = [])
    {
        if (!is_array($body)) {
            die("Body rquire array");
        }

        return $this->cURL($body);
    }

    private function cURL($body = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  "$this->api/sms",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($body),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                'Authorization: Basic ' .  $this->token
            ),
        ));

        $response = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        $resData = json_decode($response);

        $res = (object)[
            'httpStatusCode' => $httpStatusCode
        ];

        if ($httpStatusCode == 201) {
            $res->data = $resData;
        } else {
            $res->error = $resData->error;
        }


        return $res;
    }
}
