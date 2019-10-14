<?php
/**
 * Created by PhpStorm.
 * User: Jormin
 * Date: 2018/11/7
 * Time: 6:10 PM
 */

namespace Jormin\Aliyun\Mns;


use AliyunMNS\Exception\MnsException;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Requests\PublishMessageRequest;
use Jormin\Aliyun\Mns;

class MnsTopic extends Mns
{

    /**
     * 创建主题
     * @param string $topicName
     * @param null $attributes
     * @return array
     */
    public function createTopic(string $topicName, $attributes = null)
    {
        if (!$topicName) {
            return $this->error('参数有误');
        }
        $request = new CreateTopicRequest($topicName, $attributes);
        try {
            $this->client->createTopic($request);
            return $this->success('创建主题成功');
        } catch (MnsException $exception) {
            return $this->parseException($exception, '创建主题');
        }
    }

    /**
     * 删除主题
     * @param string $topicName
     * @return array
     */
    public function deleteTopic(string $topicName)
    {
        if (!$topicName) {
            return $this->error('参数有误');
        }
        try {
            $this->client->deleteTopic($topicName);
            return $this->success('删除主题成功');
        } catch (MnsException $exception) {
            return $this->parseException($exception, '删除主题');
        }
    }

    /**
     * 创建订阅
     * @param string $topicName
     * @param string $subscribeName
     * @param $endPoint
     * @param null $strategy
     * @param null $contentFormat
     * @param null $topicOwner
     * @param null $createTime
     * @param null $lastModifyTime
     * @return array
     */
    public function subscribe(string $topicName, string $subscribeName, $endPoint, $strategy = null, $contentFormat = null, $topicOwner = null, $createTime = null, $lastModifyTime = null)
    {
        if (!$topicName || !$subscribeName || !$endPoint) {
            return $this->error('参数有误');
        }
        $topic = $this->client->getTopicRef($topicName);
        $attributes = new SubscriptionAttributes($subscribeName, $endPoint, $strategy, $contentFormat, $topicName, $topicOwner, $createTime, $lastModifyTime);
        try {
            $topic->subscribe($attributes);
            return $this->success('创建订阅成功');
        } catch (MnsException $exception) {
            return $this->parseException($exception, '创建订阅');
        }
    }

    /**
     * 取消订阅
     * @param string $topicName
     * @param string $subscribeName
     * @return array
     */
    public function unsubscribe(string $topicName, string $subscribeName)
    {
        if (!$topicName || !$subscribeName) {
            return $this->error('参数有误');
        }
        $topic = $this->client->getTopicRef($topicName);
        try {
            $topic->unsubscribe($subscribeName);
            return $this->success('取消订阅成功');
        } catch (MnsException $exception) {
            return $this->parseException($exception, '取消订阅');
        }
    }

    /**
     * 发布消息
     * @param string $topicName
     * @param string $messageBody
     * @param string $messageTag
     * @return array
     */
    public function publishMessage(string $topicName, string $messageBody, string $messageTag)
    {
        if (!$topicName || !$messageBody) {
            return $this->error('参数有误');
        }
        $topic = $this->client->getTopicRef($topicName);
        $request = new PublishMessageRequest($messageBody);
        if ($messageTag) {
            $request->setMessageTag($messageTag);
        }
        try {
            $topic->publishMessage($request);
            return $this->success('发布消息成功');
        } catch (MnsException $exception) {
            return $this->parseException($exception, '发布消息');
        }
    }

}