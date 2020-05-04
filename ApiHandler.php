<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\UserCommands\ApiMethods;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

$config = yaml_parse_file('config.yaml');

class ApiHandler
{
    private static function callAPI($method, $url, $data)
    {
        $curl = curl_init();
        switch ($method){
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data)
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }
        
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        // EXECUTE:
        $result = curl_exec($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        return $result;
    }
    
    private static function prepareApiSign($key) 
    {
        return hash_hmac('sha256', time(), $key);
    }
    
    public static function prepareApiRequest($data, $method, $apiMethodUrl) 
    {
        global $config;
        $url = $config['movie_api_url'];
        $sign = self::prepareApiSign($config['movie_api_key']);
        if ($method === ApiMethods::GET_METHOD) {
            $preparedUrl = $url.'/'.$apiMethodUrl.'?expires='.time().'&sign='.$sign;
            $process = self::callAPI($method, $preparedUrl, null);
        } else {
            $preparedUrl = $url.'/'.$apiMethodUrl.'?expires='.time().'&sign='.$sign;
            self::callAPI($method, $preparedUrl, json_encode($data));
        }
        
        return json_decode($process, true);
    }
}
