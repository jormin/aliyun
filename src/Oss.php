<?php
/**
 * Created by PhpStorm.
 * User: Jormin
 * Date: 2019-03-14
 * Time: 11:23
 */

namespace Jormin\Aliyun;


use MQ\MQClient;
use OSS\OssClient;

class Oss extends BaseObject
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
     * Oss constructor.
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $endPoint
     */
    public function __construct($accessKeyId, $accessKeySecret, $endPoint)
    {
        parent::__construct($accessKeyId, $accessKeySecret);
        $this->endPoint = $endPoint;
        try {
            $this->client = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endPoint);
        } catch (\Exception $exception) {
            print $exception->getMessage();
        }
    }

    /**
     * 创建空间
     * @param string $bucket Bucket名称
     * @param string $acl ACL权限[OSS\OssClient\OssClient::OSS_ACL_TYPE_PRIVATE、OSS\OssClient\OssClient::OSS_ACL_TYPE_PUBLIC_READ、OSS\OssClient\OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE]
     * @return array
     */
    public function createBucket(string $bucket, string $acl = OssClient::OSS_ACL_TYPE_PRIVATE)
    {
        try {
            $this->client->createBucket($bucket, $acl);
            return $this->success('创建Bucket成功');
        } catch (\Exception $exception) {
            return $this->error('调用阿里云接口出错：' . $exception->getMessage());
        }
    }

    /**
     * 删除空间
     * @param string $bucket Bucket名称
     * @return array
     */
    public function deleteBucket(string $bucket)
    {
        try {
            $this->client->deleteBucket($bucket);
            return $this->success('删除Bucket成功');
        } catch (\Exception $exception) {
            return $this->error('调用阿里云接口出错：' . $exception->getMessage());
        }
    }

    /**
     * 上传内容
     * @param string $bucket Bucket名称
     * @param string $object 文件名称
     * @param string $content 文件内容
     * @return array
     */
    public function putObject(string $bucket, string $object, string $content)
    {
        try {
            $response = $this->client->putObject($bucket, $object, $content);
            return $this->success('上传文件成功', $response['info']);
        } catch (\Exception $exception) {
            return $this->error('调用阿里云接口出错：' . $exception->getMessage());
        }
    }

    /**
     * 上传文件
     * @param string $bucket Bucket名称
     * @param string $object 文件名称
     * @param string $filePath 文件路径
     * @return array
     */
    public function uploadObject(string $bucket, string $object, string $filePath)
    {
        try {
            $response = $this->client->uploadFile($bucket, $object, $filePath);
            return $this->success('上传文件成功', $response['info']);
        } catch (\Exception $exception) {
            return $this->error('调用阿里云接口出错：' . $exception->getMessage());
        }
    }

    /**
     * 获取文件内容
     * @param string $bucket Bucket名称
     * @param string $object 文件名称
     * @return array
     */
    public function getObject(string $bucket, string $object)
    {
        try {
            $content = $this->client->getObject($bucket, $object);
            return $this->success('获取文件成功', ['object' => $content]);
        } catch (\Exception $exception) {
            return $this->error('调用阿里云接口出错：' . $exception->getMessage());
        }
    }

    /**
     * 删除文件
     * @param string $bucket Bucket名称
     * @param string $object 文件名称
     * @return array
     */
    public function deleteObject(string $bucket, string $object)
    {
        try {
            $this->client->deleteObject($bucket, $object);
            return $this->success('删除文件成功');
        } catch (\Exception $exception) {
            return $this->error('调用阿里云接口出错：' . $exception->getMessage());
        }
    }

    /**
     * 批量删除文件
     * @param string $bucket Bucket名称
     * @param array $objects 文件名称数组
     * @return array
     */
    public function deleteObjects(string $bucket, array $objects)
    {
        try {
            $this->client->deleteObjects($bucket, $objects);
            return $this->success('批量删除文件成功');
        } catch (\Exception $exception) {
            return $this->error('调用阿里云接口出错：' . $exception->getMessage());
        }
    }

    /**
     * 获取文件列表
     * @param string $bucket Bucket名称
     * @param string $marker 起始位置标记
     * @param int $maxKeys 最大条数
     * @param string $delimiter 用于对Object名字进行分组的字符。所有名字包含指定的前缀且第一次出现Delimiter字符之间的Object作为一组元素
     * @param string $prefix 前缀
     * @return array
     */
    public function listObjects($bucket, $marker = null, $maxKeys = 10, $delimiter = '', $prefix = '')
    {
        try {
            $options = [];
            $marker && $options['marker'] = $marker;
            $delimiter && $options['delimiter'] = $delimiter;
            $maxKeys && $options['max-keys'] = $maxKeys;
            $prefix && $options['prefix'] = $prefix;
            $objects = $this->client->listObjects($bucket, $options)->getObjectList();
            return $this->success('获取成功', ['objects' => $objects]);
        } catch (\Exception $exception) {
            return $this->error('调用阿里云接口出错：' . $exception->getMessage());
        }
    }

}