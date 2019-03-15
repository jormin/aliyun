<?php
/**
 * Created by PhpStorm.
 * User: Jormin
 * Date: 2019-03-14
 * Time: 11:23
 */

namespace Jormin\Aliyun;


use MQ\Exception\AckMessageException;
use MQ\Exception\MessageNotExistException;
use MQ\Model\Message;
use MQ\Model\TopicMessage;
use MQ\MQClient;

class Mq extends BaseObject
{

    /**
     * @var MQClient
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
        $this->client = new MQClient($this->endPoint, $this->accessKeyId, $this->accessKeySecret);
    }

    /**
     * 获取生产者
     * @param string $instanceId 实例ID
     * @param string $topicName 主题名称
     * @return \MQ\MQProducer
     */
    public function getProducer($instanceId, $topicName)
    {
        return $this->client->getProducer($instanceId, $topicName);
    }

    /**
     * 获取消费者
     * @param string $instanceId 实例ID
     * @param string $topicName 主题名称
     * @param string $consumer 消费者标识
     * @param null $messageTag
     * @return \MQ\MQConsumer
     */
    public function getConsumer($instanceId, $topicName, $consumer, $messageTag = NULL)
    {
        return $this->client->getConsumer($instanceId, $topicName, $consumer, $messageTag);
    }

    /**
     * 发送消息
     * @param string $instanceId 实例ID
     * @param string $topicName 主题名称
     * @param string $message 消息内容
     * @return array
     */
    public function publishMessage($instanceId, $topicName, $message)
    {
        $producer = $this->getProducer($instanceId, $topicName);
        try {
            $producer->publishMessage(new TopicMessage($message));
            return $this->success('发送成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    /**
     * 消费消息
     * @param string $instanceId 实例ID
     * @param string $topicName 主题名称
     * @param string $groupId 消费者标识
     * @param string $callback 业务回调，参数为 MQ\Model\Message 对象
     * @param string $exceptionCallback 消费消息异常处理，参数为 \Exception 对象，不传此参数默认打印异常信息到控制台
     * @param string $ackExceptionCallback 确认消息异常处理，参数为 MQ\Exception\AckMessageException 对象，不传参数默认打印异常信息到控制台
     * @param bool $ackMessage 是否确认消息，默认为true
     * @param int $numOfMessages 一次最多消费消息条数，，默认3条(最多可设置为16条)
     * @param int $waitSeconds 长轮询时间，默认3秒（最多可设置为30秒）
     */
    public function consumeMessage($instanceId, $topicName, $groupId, $callback, $exceptionCallback, $ackExceptionCallback, $ackMessage = true, $numOfMessages = 3, $waitSeconds = 3)
    {
        $consumer = $this->getConsumer($instanceId, $topicName, $groupId);
        while (True) {
            try {
                // 长轮询表示如果topic没有消息则请求会在服务端挂住3s，3s内如果有消息可以消费则立即返回
                $messages = $consumer->consumeMessage(
                    $numOfMessages, // 一次最多消费3条(最多可设置为16条)
                    $waitSeconds // 长轮询时间3秒（最多可设置为30秒）
                );
            } catch (\Exception $e) {
                if ($e instanceof MessageNotExistException) {
                    // 没有消息可以消费，接着轮询
                    continue;
                }
                if (!$exceptionCallback) {
                    $exceptionCallback = function (\Exception $e) {
                        print_r($e->getMessage() . "\n");
                    };
                }
                call_user_func($exceptionCallback, $e);
                sleep(3);
                continue;
            }
            // 处理业务逻辑
            $receiptHandles = array();
            foreach ($messages as $message) {
                // $message->getNextConsumeTime()前若不确认消息消费成功，则消息会重复消费
                $ackMessage && $receiptHandles[] = $message->getReceiptHandle();
                if (!$callback) {
                    $callback = function (Message $message) {
                        printf("MessageID:%s TAG:%s BODY:%s \nPublishTime:%d, FirstConsumeTime:%d, \nConsumedTimes:%d\n",
                            $message->getMessageId(), $message->getMessageTag(), $message->getMessageBody(),
                            $message->getPublishTime(), $message->getFirstConsumeTime(), $message->getConsumedTimes());
                    };
                }
                call_user_func($callback, $message);
            }
            // 消息句柄有时间戳，同一条消息每次消费拿到的都不一样
            if ($ackMessage) {
                try {
                    $consumer->ackMessage($receiptHandles);
                } catch (\Exception $e) {
                    if ($e instanceof AckMessageException) {
                        // 某些消息的句柄可能超时了会导致确认不成功
                        if (!$ackExceptionCallback) {
                            $ackExceptionCallback = function (AckMessageException $e) {
                                printf("Ack Error, RequestId:%s\n", $e->getRequestId());
                                foreach ($e->getAckMessageErrorItems() as $errorItem) {
                                    printf("\tReceiptHandle:%s, ErrorCode:%s, ErrorMsg:%s\n", $errorItem->getReceiptHandle(), $errorItem->getErrorCode(), $errorItem->getErrorCode());
                                }
                            };
                        }
                        call_user_func($ackExceptionCallback, $e);
                    }
                }
            }
        }
    }
}