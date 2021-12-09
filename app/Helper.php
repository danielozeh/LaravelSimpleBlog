<?php

namespace App;

class Helper {

    static function generateCode($length) {
        $code = substr(md5(uniqid() . "" . time()), -$length);
        return $code;
    }

    static function getRealIpAddr() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
            if (array_key_exists($key, $_SERVER) === true){
                foreach (explode(',', $_SERVER[$key]) as $ip){
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                        return $ip;
                    }
                }
            }
        }
    }

    static function getRealUserLocation() {
        $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=".$this->getRealIpAddr());
        $country =  $xml->geoplugin_countryName;               
        $state = $xml->geoplugin_regionName;
        $region =   $xml->geoplugin_regionName;
        $user_browser = $_SERVER['HTTP_USER_AGENT'];

        $ipInfo = file_get_contents('http://ip-api.com/json/' .$this->getRealIpAddr());
        $ipInfo = json_decode($ipInfo);
        $timezone = $ipInfo->timezone;

        return response()->json([
            "country" => $country,
            "state" => $state,
            "region" => $region,
            "timezone" => $timezone,
            "user_browser" => $user_browser
        ]);
    }

    static function imagePath() {
        return env('IMG_PATH');
    }
}