<?php

$mapping = array(
    'MQ\AsyncCallback' => __DIR__ . '/AsyncCallback',
    'MQ\Config' => __DIR__ . '/Config.php',
    'MQ\Constants' => __DIR__ . '/Constants.php',
    'MQ\MQClient' => __DIR__ . '/MQClient.php',
    'MQ\MQConsumer' => __DIR__ . '/MQConsumer.php',
    'MQ\MQProducer' => __DIR__ . '/MQProducer.php',
    'MQ\Common\XMLParser' => __DIR__ . '/Common/XMLParser.php',
    'MQ\Exception\AckMessageException' => __DIR__ . '/Exception/AckMessageException.php',
    'MQ\Exception\InvalidArgumentException' => __DIR__ . '/Exception/InvalidArgumentException.php',
    'MQ\Exception\MalformedXMLException' => __DIR__ . '/Exception/MalformedXMLException.php',
    'MQ\Exception\MessageNotExistException' => __DIR__ . '/Exception/MessageNotExistException.php',
    'MQ\Exception\MQException' => __DIR__ . '/Exception/MQException.php',
    'MQ\Exception\ReceiptHandleErrorException' => __DIR__ . '/Exception/ReceiptHandleErrorException.php',
    'MQ\Exception\TopicNotExistException' => __DIR__ . '/Exception/TopicNotExistException.php',
    'MQ\Http\HttpClient' => __DIR__ . '/Http/HttpClient.php',
    'MQ\Model\AckMessageErrorItem' => __DIR__ . '/Model/AckMessageErrorItem.php',
    'MQ\Model\Message' => __DIR__ . '/Model/Message.php',
    'MQ\Model\TopicMessage' => __DIR__ . '/Model/TopicMessage.php',
    'MQ\Requests\AckMessageRequest' => __DIR__ . '/Requests/AckMessageRequest.php',
    'MQ\Requests\BaseRequest' => __DIR__ . '/Requests/BaseRequest.php',
    'MQ\Requests\ConsumeMessageRequest' => __DIR__ . '/Requests/ConsumeMessageRequest.php',
    'MQ\Requests\PublishMessageRequest' => __DIR__ . '/Requests/PublishMessageRequest.php',
    'MQ\Responses\AckMessageResponse' => __DIR__ . '/Responses/AckMessageResponse.php',
    'MQ\Responses\BaseResponse' => __DIR__ . '/Responses/BaseResponse.php',
    'MQ\Responses\ConsumeMessageResponse' => __DIR__ . '/Responses/ConsumeMessageResponse.php',
    'MQ\Responses\MQPromise' => __DIR__ . '/Responses/MQPromise.php',
    'MQ\Responses\PublishMessageResponse' => __DIR__ . '/Responses/PublishMessageResponse.php',
    'MQ\Signature\Signature' => __DIR__ . '/Signature/Signature.php',
    'MQ\Traits\MessagePropertiesForConsume' => __DIR__ . '/Traits/MessagePropertiesForConsume.php',
    'MQ\Traits\MessagePropertiesForPublish' => __DIR__ . '/Traits/MessagePropertiesForPublish.php'
);

spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require $mapping[$class];
    }
}, true);
