<?php

namespace Jormin\Aliyun;

include_once dirname(__FILE__).'/../sdk/aliyun-openapi-php-sdk/aliyun-php-sdk-core/Config.php';

/**
 * Class BaseObject
 * @package Jormin\Qiniu
 */
class BaseObject{

    protected $accessKeyId, $accessKeySecret, $client;

    /**
     * BaseObject constructor.
     * @param $accessKeyId
     * @param $accessKeySecret
     */
    public function __construct($accessKeyId, $accessKeySecret)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $iClientProfile = \DefaultProfile::getProfile("cn-hangzhou", $accessKeyId, $accessKeySecret);
        $this->client = new \DefaultAcsClient($iClientProfile);
    }

    /**
     * 失败
     * @param $message
     * @param null $data
     * @return array
     */
    public function error($message, $data=null){
        is_object($data) && $data = (array)$data;
        $return = ['success' => false, 'message' => $message, 'data'=>$data];
        return $return;
    }

    /**
     * 成功
     * @param $message
     * @param null $data
     * @return array
     */
    public function success($message, $data=null){
        is_object($data) && $data = (array)$data;
        $return = ['success' => true, 'message' => $message, 'data'=>$data];
        return $return;
    }

    /**
     * 嵌套对象转数组
     * @param $data
     * @return array
     */
    public function objectToArray($data){
        if(is_object($data)){
            $data = (array)$data;
        }
        foreach ($data as $key => $item){
            $data[$key] = $this->objectToArray($item);
        }
        return $data;
    }
}
