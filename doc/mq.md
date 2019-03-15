MQ服务文档

### 使用

> 可以获得生产者和消费者对象后自行处理发送和消费，也可以使用封装好的方法进行处理

1. 生成MQ对象

    ``` php
    $accessKeyId = 'your access key id';
    $accessKeySecret = 'your access key secret';
    $endPoint = 'your endPoint';
    $mq = new \Jormin\Aliyun\Mq($accessKeyId, $accessKeySecret, $endPoint);
    ```

2. 获取生产者对象

    ```php
   $instanceId = 'your instance id';
   $topic = 'your topic name';
   $producer = $mq->getProducer($instanceId, $topic);
    ```

3. 获取消费者对象

    ```php
    $groupId = 'your group id';
    $consumer = $mq->getConsumer($instanceId, $topic, $groupId);
    ```

4. 发送消息

    ```php
    /**
     * 发送消息
     * @param string $instanceId 实例ID
     * @param string $topicName 主题名称
     * @param string $message 消息内容
     * @return array
     */
    $mq->publishMessage($instanceId, $topicName, $message)
    ```

5. 消费消息

    - 此功能需要常驻后台处理(Http 轮询)
    - 执行 $message->getNextConsumeTime() 前若不确认消息消费成功，则消息会重复消费

    ```php
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
    $mq->consumeMessage($instanceId, $topicName, $groupId, $callback, $exceptionCallback, $ackExceptionCallback, $ackMessage = true, $numOfMessages = 3, $waitSeconds = 3)
    ```
    
    示例
    
    ```
    // 定义参数
    $accessKeyId = 'xxx';
    $accessKeySecret = 'xxx';
    $endPoint = 'xxx';
    $instanceId = 'xxx';
    $topic = 'xxx';
    $groupId = 'xxx';
    // 获取 MQ 对象
    $mq = new \Jormin\Aliyun\Mq($accessKeyId, $accessKeySecret, $endPoint);
    // 业务回调
    $callback = function (\MQ\Model\Message $message) {
        print_r($message->getMessageBody());
    };
    // 消费消息异常处理
    $exceptionCallback = function (Exception $exception) {
        print_r(get_class($exception) . '----' . $exception->getMessage() . PHP_EOL);
    };
    // 确认消息异常处理
    $ackExceptionCallback = function (\MQ\Exception\AckMessageException $ackMessageException) {
        print_r(get_class($ackMessageException) . '----' . $ackMessageException->getMessage() . PHP_EOL);
    };
    // 消费消息
    $mq->consumeMessage($instanceId, $topic, $groupId, $callback, $exceptionCallback, $ackExceptionCallback, true, 3, 3);
    ```