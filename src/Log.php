<?php

namespace Jormin\Aliyun;

include_once dirname(__FILE__) . '/../sdk/aliyun-log-php-sdk/Log_Autoload.php';

/**
 * Class Log
 * @package Jormin\Aliyun
 */
class Log extends BaseObject
{

    /**
     * @var \Aliyun_Log_Client
     */
    protected $client;

    /**
     * @var string Endpoint
     */
    protected $endPoint;

    /**
     * Log constructor.
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $endPoint
     */
    public function __construct($accessKeyId, $accessKeySecret, $endPoint)
    {
        parent::__construct($accessKeyId, $accessKeySecret);
        $this->endPoint = $endPoint;
        $this->client = new \Aliyun_Log_Client($this->endPoint, $this->accessKeyId, $this->accessKeySecret);
    }

    /**
     * 解析失败异常
     * @param \Aliyun_Log_Exception $exception
     * @param string $messagePrefix
     * @param null $data
     * @return array
     */
    protected function parseException(\Aliyun_Log_Exception $exception, string $messagePrefix, $data = null)
    {
        return $this->error($messagePrefix . '失败，' . $exception->getMessage() . '[' . $exception->getErrorCode() . ']', $data);
    }

    /**
     * 获取LogStore列表
     * @param string $project
     * @return array
     */
    public function getLogStores(string $project)
    {
        try {
            $request = new \Aliyun_Log_Models_ListLogstoresRequest($project);
            $response = $this->client->listLogstores($request);
            $logStores = $response->getLogstores();
            return $this->success('获取成功', $logStores);
        } catch (\Aliyun_Log_Exception $exception) {
            return $this->parseException($exception, '获取LogStore列表');
        }
    }

    /**
     * 获取日志
     * @param string $project
     * @param string $logStore
     * @param int $from
     * @param int $to
     * @param string $topic
     * @param string $query
     * @param int $line
     * @param int $offset
     * @param bool $reverse
     * @return array
     */
    public function getLogs(string $project, string $logStore, int $from, int $to, string $topic, string $query, int $line = 100, $offset = 0, $reverse = false)
    {
        try {
            $request = new \Aliyun_Log_Models_GetLogsRequest($project, $logStore, $from, $to, $topic, $query, $line, $offset, $reverse);
            $response = $this->client->getLogs($request);
            $logs = $response->getLogs();
            foreach ($logs as $key => $log){
                $contents = $log->getContents();
                $logs[$key] = [
                    'source' => $log->getSource(),
                    'time' => $log->getTime(),
                    'hostname' => $contents['__tag__:__hostname__'],
                    'packId' => $contents['__tag__:__pack_id__'],
                    'path' => $contents['__tag__:__path__'],
                    'receiveTime' => $contents['__tag__:__receive_time__'],
                    'topic' => $contents['__topic__'],
                    'content' => $contents['content']
                ];
            }
            return $this->success('获取成功', $logs);
        } catch (\Aliyun_Log_Exception $exception) {
            return $this->parseException($exception, '获取LogStore列表');
        }
    }

}
