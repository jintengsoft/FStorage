<?php

namespace jinteng\filestorage;
use Exception;

// 锦腾文件上传服务
// demo code @ cosine
// composer build @ Adike

class FStorage
{
    public static $appName;
    public static $appKey;
    public static $appKeyId;
    public static $url;
    public static $postFields;

    public static function test()
    {
        echo FStorage::$appName;
    }

    // public function __construct($url, $appName, $appKey, $appKeyId)
    // {
    //     $this->url      = $url;
    //     $this->appKey   = $appKey;
    //     $this->appKeyId = $appKeyId;
    //     $this->appName  = $appName;
    // }

    public static function uploadFile($fileName)
    {
        $token   = self::getToken();
        $MD5_file = md5_file($fileName['tmp_name']);
        $appName = self::$appName;
        $appKey = self::$appKey;
        $key = sha1("{$appName}:$MD5_file:$token:{$appKey}");

        self::$postFields['token'] = $token;
        self::$postFields['key']   = $key;

        self::$postFields['file'] = new \CURLFile(realpath($fileName['tmp_name']));
        self::$postFields['file_name'] = $fileName['name'];

        $responseText = self::getHttpRequest('upload');

        if ($responseText && ($response = json_decode($responseText))) {
            if ($response->status == 'OK' && $response->data) {
                return $response->data->url;
            }

            throw new Exception('Can not upload file: ' . $response->error->msg, $response->error->code);
        }
        throw new Exception('Can not upload file: Unknown error');
    }

    private static function getToken()
    {
        $salt = md5(rand(0, 999999) . time());
        self::$postFields['app']   = self::$appName;
        self::$postFields['keyId'] = self::$appKeyId;
        self::$postFields['salt']  = $salt;
        $appName = self::$appName;
        $appKey = self::$appKey;
        self::$postFields['key']   = sha1("{$appName}:$salt:{$appKey}");

        $responseText = self::getHttpRequest('token');


        if ($responseText && ($response = json_decode($responseText))) {

            if ($response->status == 'OK' && $response->data) {
                return $response->data->token;
            }

            throw new Exception('Can not get token: ' . $response->error->msg, $response->error->code);
        }

        throw new Exception('Can not get token: Unknown error');
    }

    protected static function getHttpRequest($action)
    {
        $method = null;

        if ($action == 'upload'){
            $method = 'POST';
        }

        return self::postCurl(self::$url . '?a=' . $action, $method);
    }


    private static function postCurl($url, $method = '', $second = 30)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);

        if ($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, self::$postFields);

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }
}