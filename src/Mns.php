<?php

namespace Jormin\Aliyun;

include_once dirname(__FILE__).'/../sdk/aliyun-mns-php-sdk/mns-autoloader.php';
use AliyunMNS\Client;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Requests\PublishMessageRequest;

/**
 * Class Mns
 * @package Jormin\Aliyun
 */
class Mns extends BaseObject {

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string Endpoint
     */
    protected $endPoint;

    /**
     * Mns constructor.
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $endPoint
     */
    public function __construct($accessKeyId, $accessKeySecret, $endPoint)
    {
        parent::__construct($accessKeyId, $accessKeySecret);
        $this->endPoint = $endPoint;
        $this->client = new Client($this->endPoint, $this->accessKeyId, $this->accessKeySecret);
    }

    /**
     * 解析失败异常
     * @param MnsException $exception
     * @param string $messagePrefix
     * @param null $data
     * @return array
     */
    protected function parseException(MnsException $exception, string $messagePrefix, $data=null){
        return $this->error($messagePrefix.'失败，'.$exception->getMessage().'['.$exception->getMnsErrorCode().']', $data);
    }

    /**
     * 封装参数属性对象
     * @param $class
     * @param array $values
     * @param array $args
     * @param array $excludeArgs
     * @return mixed
     */
    protected function makeAttributesObject($class, array $values, array $args, array $excludeArgs){
        $attributes = [];
        foreach ($args as $arg){
            if(in_array($arg, $excludeArgs)){
                $attributes[] = null;
            }else{
                $attributes[] = $values[$arg] ?? null;
            }
        }
        return new $class(...$attributes);
    }

    /**
     * 解析对象属性
     * @param $class
     * @param $object
     * @return array
     */
    protected function parseObjectData($class, $object){
        $result = [];
        $reflection = new \ReflectionClass($class);
        foreach ($reflection->getProperties() as $property){
            $propertyName = $property->name;
            $method = 'get'.ucfirst($propertyName);
            $result[$propertyName] = $reflection->hasMethod($method) ? $object->$method() : null;
        }
        return $result;
    }

}
