<?php
/**
 * Created by PhpStorm.
 * User: Jormin
 * Date: 2018/11/7
 * Time: 1:00 PM
 */

namespace Jormin\Aliyun\Mns;

use AliyunMNS\Exception\MnsException;
use AliyunMNS\Model\Message;
use AliyunMNS\Model\QueueAttributes;
use AliyunMNS\Model\SendMessageRequestItem;
use AliyunMNS\Requests\BatchReceiveMessageRequest;
use AliyunMNS\Requests\BatchSendMessageRequest;
use AliyunMNS\Requests\ListQueueRequest;
use AliyunMNS\Responses\PeekMessageResponse;
use AliyunMNS\Responses\ReceiveMessageResponse;
use Jormin\Aliyun\Mns;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Requests\SendMessageRequest;

class MnsQueue extends Mns
{

    /**
     * 获取队列列表
     * @param int $retNum
     * @param null $prefix
     * @param null $marker
     * @return array
     */
    public function getQueues($retNum=1000, $prefix=null, $marker=null){
        $request = new ListQueueRequest($retNum, $prefix, $marker);
        $response = $this->client->listQueue($request);
        return $this->success('获取队列列表成功', ['queues'=>$response->getQueueNames(), 'nextMarker'=>$response->getNextMarker()]);
    }

    /**
     * 创建队列
     * @param string $queueName
     * @param array $attributes
     * @return array
     */
    public function createQueue(string $queueName, array $attributes=array()) {
        if(!$queueName){
            return $this->error('参数有误');
        }
        $request = new CreateQueueRequest($queueName, $this->makeQueueAttributes($attributes));
        try{
            $this->client->createQueue($request);
            return $this->success('创建队列成功');
        }catch(MnsException $exception){
            return $this->parseException($exception, '创建队列');
        }
    }

    /**
     * 获取队列属性
     * @param string $queueName
     * @return array
     */
    public function getAttributes(string $queueName) {
        if(!$queueName){
            return $this->error('参数有误');
        }
        try{
            $queue = $this->client->getQueueRef($queueName);
            $response = $queue->getAttribute();
            return $this->success('获取队列属性成功', $this->parseObjectData(QueueAttributes::class, $response->getQueueAttributes()));
        }catch(MnsException $exception){
            return $this->parseException($exception, '获取队列属性');
        }
    }

    /**
     * 设置队列属性
     * @param string $queueName
     * @param array $attributes
     * @return array
     */
    public function setAttributes(string $queueName, array $attributes=array()) {
        if(!$queueName){
            return $this->error('参数有误');
        }
        try{
            $queue = $this->client->getQueueRef($queueName);
            $queue->setAttribute($this->makeQueueAttributes($attributes));
            return $this->success('设置队列属性成功');
        }catch(MnsException $exception){
            return $this->parseException($exception, '设置队列属性');
        }
    }

    /**
     * 删除队列
     * @param string $queueName
     * @return array
     */
    public function deleteQueue(string $queueName) {
        if(!$queueName){
            return $this->error('参数有误');
        }
        try{
            $this->client->deleteQueue($queueName);
            return $this->success('删除队列成功');
        }catch(MnsException $exception){
            return $this->parseException($exception, '删除队列');
        }
    }

    /**
     * 发送消息
     * @param string $queueName
     * @param $message
     * @param int $delaySeconds
     * @param int $priority
     * @param bool $base64
     * @return array
     */
    public function sendMessage(string $queueName, $message, int $delaySeconds=0, int $priority=8, bool $base64=true) {
        if(!$queueName || !$message){
            return $this->error('参数有误');
        }
        try{
            $queue = $this->client->getQueueRef($queueName);
            if(is_array($message)){
                $requestItems = [];
                foreach ($message as $item){
                    $requestItem = new SendMessageRequestItem($item, $delaySeconds, $priority);
                    $requestItems[] = $requestItem;
                }
                $request = new BatchSendMessageRequest($requestItems, $base64);
                $queue->batchSendMessage($request);
            }else{
                $request = new SendMessageRequest($message, $delaySeconds, $priority, $base64);
                $queue->sendMessage($request);
            }
            return $this->success('发送消息成功');
        }catch(MnsException $exception){
            return $this->parseException($exception, '发送消息');
        }
    }

    /**
     * 消费并删除消息
     * @param string $queueName
     * @param int $waitSeconds
     * @param mixed $callback
     * @param bool $delete
     * @return array
     */
    public function dealMessage(string $queueName, int $waitSeconds, $callback, bool $delete=false){
        if(!$queueName || $waitSeconds < 0 || $waitSeconds > 30){
            return $this->error('参数有误');
        }
        if((!is_array($callback) && !is_callable($callback)) || (is_array($callback) && (count($callback) < 2 || !is_callable($callback[1])))){
            return $this->error('回调函数有误');
        }
        try{
            $queue = $this->client->getQueueRef($queueName);
            $response = $queue->receiveMessage(30);
            if(is_array($callback)){
                call_user_func($callback[0]->$callback[1](), $response);
            }else{
                call_user_func($callback, $response);
            }
            $responseData = $this->parseObjectData(ReceiveMessageResponse::class, $response);
            if($delete === false){
                return $this->success('消费消息成功', $responseData);
            }
            try{
                $queue->deleteMessage($response->getReceiptHandle());
                return $this->success('消费并删除消息成功', $responseData);
            }catch (MnsException $exception){
                return $this->parseException($exception, '消费并删除消息', $responseData);
            }
        }catch (MnsException $exception){
            return $this->parseException($exception, '消费消息');
        }
    }

    /**
     * 消费消息
     * @param string $queueName
     * @param int $amount
     * @param int $waitSeconds
     * @return array
     */
    public function receiveMessage(string $queueName, $amount=1, int $waitSeconds){
        if(!$queueName || !$amount || $waitSeconds < 0 || $waitSeconds > 30){
            return $this->error('参数有误');
        }
        try{
            $responseData = [];
            $queue = $this->client->getQueueRef($queueName);
            if($amount > 1){
                $request = new BatchReceiveMessageRequest($amount, 30);
                $response = $queue->batchReceiveMessage($request);
                foreach ($response->getMessages() as $messageObject){
                    $responseData[] = $this->parseObjectData(Message::class, $messageObject);
                }
            }else{
                $response = $queue->receiveMessage(30);
                $responseData = $this->parseObjectData(ReceiveMessageResponse::class, $response);
            }
            return $this->success('消费消息成功', $responseData);
        }catch (MnsException $exception){
            return $this->parseException($exception, '消费消息');
        }
    }

    /**
     * 查看消息
     * @param string $queueName
     * @param int $amount
     * @return array
     */
    public function peekMessage(string $queueName, $amount=1){
        if(!$queueName || !$amount){
            return $this->error('参数有误');
        }
        try{
            $responseData = [];
            $queue = $this->client->getQueueRef($queueName);
            if($amount > 1){
                $response = $queue->batchPeekMessage($amount);
                foreach ($response->getMessages() as $messageObject){
                    $responseData[] = $this->parseObjectData(Message::class, $messageObject);
                }
            }else{
                $response = $queue->peekMessage();
                $responseData = $this->parseObjectData(PeekMessageResponse::class, $response);
            }
            return $this->success('查看消息成功', $responseData);
        }catch (MnsException $exception){
            return $this->parseException($exception, '查看消息');
        }
    }

    /**
     * 删除消息
     * @param string $queueName
     * @param $receiptHandle
     * @return array
     */
    public function deleteMessage(string $queueName, $receiptHandle){
        if(!$queueName || !$receiptHandle){
            return $this->error('参数有误');
        }
        try{
            $queue = $this->client->getQueueRef($queueName);
            if(is_array($receiptHandle)){
                $method = 'batchDeleteMessage';
            }else{
                $method = 'deleteMessage';
            }
            $queue->$method($receiptHandle);
            return $this->success('删除消息成功');
        }catch (MnsException $exception){
            return $this->parseException($exception, '删除消息');
        }
    }

    /**
     * 修改消息可见时间
     * @param string $queueName
     * @param $receiptHandle
     * @param int $visibilityTimeout
     * @return array
     */
    public function changeMessageVisibility(string $queueName, $receiptHandle, int $visibilityTimeout){
        if(!$queueName || !$receiptHandle){
            return $this->error('参数有误');
        }
        try{
            $queue = $this->client->getQueueRef($queueName);
            $queue->changeMessageVisibility($receiptHandle, $visibilityTimeout);
            return $this->success('修改消息可见时间成功');
        }catch (MnsException $exception){
            return $this->parseException($exception, '修改消息可见时间');
        }
    }

    /**
     * 封装队列属性
     * @param $attributes
     * @return mixed|null
     */
    private function makeQueueAttributes($attributes){
        $queueAttributes = null;
        if(count($attributes)){
            $args = ['delaySeconds', 'maximumMessageSize', 'messageRetentionPeriod', 'visibilityTimeout', 'pollingWaitSeconds', 'queueName', 'createTime', 'lastModifyTime', 'activeMessages',
                'inactiveMessages', 'delayMessages', 'LoggingEnabled'];
            $excludeArgs = ['queueName', 'createTime', 'lastModifyTime', 'activeMessages', 'inactiveMessages', 'delayMessages'];
            $queueAttributes = $this->makeAttributesObject(QueueAttributes::class, $attributes, $args, $excludeArgs);
        }
        return $queueAttributes;
    }

}