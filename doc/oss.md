OSS服务文档

### 使用

> 本服务基于官方扩展包封装，只封装了部分功能，可自行安装官方包 [aliyuncs/oss-sdk-php](https://github.com/aliyun/aliyun-oss-php-sdk?spm=a2c4g.11186623.2.11.f531c839dGa9mX) 开发

1. 生成OSS对象

    ``` php
    $accessKeyId = 'your access key id';
    $accessKeySecret = 'your access key secret';
    $endPoint = 'your endPoint';
    $oss = new \Jormin\Aliyun\Oss($accessKeyId, $accessKeySecret, $endPoint);
    ```

2. 创建空间

    ```php
    /**
     * 创建空间
     * @param string $bucket Bucket名称
     * @param string $acl ACL权限[OSS\OssClient\OssClient::OSS_ACL_TYPE_PRIVATE、OSS\OssClient\OssClient::OSS_ACL_TYPE_PUBLIC_READ、OSS\OssClient\OssClient::OSS_ACL_TYPE_PUBLIC_READ_WRITE]
     * @return array
     */
    $oss->createBucket(string $bucket, string $acl = OssClient::OSS_ACL_TYPE_PRIVATE)
    ```

3. 删除空间

    ```php
    /**
     * 删除空间
     * @param string $bucket Bucket名称
     * @return array
     */
    $oss->deleteBucket(string $bucket)
    ```

4. 上传内容

    ```php
    /**
     * 上传内容
     * @param string $bucket Bucket名称
     * @param string $object 文件名称
     * @param string $content 文件内容
     * @return array
     */
    $oss->putObject(string $bucket, string $object, string $content)
    ```

5. 上传文件

    ```php
    /**
     * 上传文件
     * @param string $bucket Bucket名称
     * @param string $object 文件名称
     * @param string $filePath 文件路径
     * @return array
     */
    $oss->uploadObject(string $bucket, string $object, string $filePath)
    ```

6. 获取文件内容

    ```php
    /**
     * 获取文件内容
     * @param string $bucket Bucket名称
     * @param string $object 文件名称
     * @return array
     */
    $oss->getObject(string $bucket, string $object)
    ```

7. 删除文件

    ```php
    /**
     * 删除文件
     * @param string $bucket Bucket名称
     * @param string $object 文件名称
     * @return array
     */
    $oss->deleteObject(string $bucket, string $object)
    ```

8. 批量删除文件

    ```php
    /**
     * 批量删除文件
     * @param string $bucket Bucket名称
     * @param array $objects 文件名称数组
     * @return array
     */
    $oss->deleteObjects(string $bucket, array $objects)
    ```

9. 获取文件列表

    ```php
    /**
     * 获取文件列表
     * @param string $bucket Bucket名称
     * @param string $marker 起始位置标记
     * @param int $maxKeys 最大条数
     * @param string $delimiter 用于对Object名字进行分组的字符。所有名字包含指定的前缀且第一次出现Delimiter字符之间的Object作为一组元素
     * @param string $prefix 前缀
     * @return array
     */
    $oss->listObjects($bucket, $marker = null, $maxKeys = 10, $delimiter = '', $prefix = '')
    ```