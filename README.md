基于阿里云官方SDK的扩展包

## 安装

``` bash
$ composer require jormin/aliyun -vvv
```

## 通用配置

 - accessKeyId: 阿里云Access Key ID
 - accessKeySecret: 阿里云Access Key Secret

## 通用响应

| 参数  | 类型  | 是否必须  | 描述  |
| ------------ | ------------ | ------------ | ------------ |
| success| bool | 是 | true：操作成功 false:操作失败 |
| message | string | 是 | 结果说明 |
| data | array | 否 | 返回数据 |


## 功能文档

- [移动推送](doc/push.md)

- [短信服务](doc/sms.md)

- [开放搜索服务](doc/open-search.md)

- [RocketMQ](doc/mq.md)

## 参考项目

1. [aliyun/aliyun-openapi-php-sdk](https://github.com/aliyun/aliyun-openapi-php-sdk?spm=a2c4g.11186623.2.3.cScbtO)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
