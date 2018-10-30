开放搜索服务文档

### 使用

> 点击接口名称可访问阿里云开放搜索官方文档

1. 生成开放搜索对象

    ``` php
    $accessKeyId = 'your access key id';
    $accessKeySecret = 'your access key secret';
    $host = 'aliyun open-search public api domain';
    $openSearch = new \Jormin\Aliyun\OpenSearch($accessKeyId, $accessKeySecret, $host, [$debug=false]);
    ```

2. [获取App列表](https://help.aliyun.com/document_detail/57153.html?spm=a2c4g.11186623.2.17.78037349nXMfc2)

    ```php
    /**
     * @param int $page 获取第几页应用列表，该参数值必须大于0，否者会报错
     * @param int $size 每页返回的应用个数，该参数值必须大于或等于0，否者会报错
     * @return array
     */
    $openSearch->getApps([$page=1, $size=10]);
    ```

3. [获取App信息](https://help.aliyun.com/document_detail/57153.html?spm=a2c4g.11186623.2.17.78037349nXMfc2)

    ```php
    /**
     * @param string $appName App名称
     * @return array
     */
    $openSearch->getAppInfo($appName);
    ```

4. [上传文档](https://help.aliyun.com/document_detail/57154.html?spm=a2c4g.11186623.2.18.78037349FFXcJm)

    ```php
    /**
     * @param string $appName App名称
     * @param string $tableName 表名称
     * @param array $data 推送二维数据，每个元素包含cmd和fields两个字段，其中fields字段是个数组，标准版仅支持ADD和DELETE操作，高级版额外支持UPDATE操作
     * @return array
     */
    $openSearch->pushDoc($appName, $tableName, $data);
    ```

5. [搜索](https://help.aliyun.com/document_detail/57155.html?spm=a2c4g.11186623.2.19.780373495hDU08)

    ```php
    /**
     * @param string $appName App名称
     * @param string $query 查询语句
     * @param string $firstRankName 粗排名称，默认 default
     * @param string $secondRankName 精排名称，默认 default
     * @param integer $start 从搜索结果中第start个文档开始返回，取值范围[0,5000]
     * @param integer $hits 返回文档的最大数量，取值范围[0,500]
     * @param null $filter 文档过滤条件
     * @param null $kvpairs kvpairs子句
     * @param array $qp 指定要使用的查询分析规则，多个规则使用英文逗号（,）分隔
     * @param array $fetchFields 本次查询需要的字段内容
     * @param array $sort 文档排序规则，二维数组，每个元素需要设置排序字段(field)及排序规则(order)，排序规则可选值有 OpenSearch::SORT_INCREASE及OpenSearch::SORT_DECREASE
     * @param array $distinct distinct子句
     * @param array $agregate aggregate子句
     * @param array $disable 关闭指定已生效的参数功能，目前仅支持禁用 qp，summary，first_rank，second_rank 等参数功能
     * @param array $summary 动态摘要的配置
     * @return array
     */
    $openSearch->search($appName, $query, [$firstRankName='default', $secondRankName='default', $start=0, $hits=10,$filter=null, $kvpairs=null, $qp=[], $fetchFields=[], $sort=[], $distinct=[], $agregate=[], $disable=[], $summary=[]]);
    ```

6. [扫描](https://help.aliyun.com/document_detail/57155.html?spm=a2c4g.11186623.2.19.780373495hDU08)

    扫描需要传递scrollID，第一次扫描时需要**先传空获取scrollID，然后再进行扫描**;扫描支持的参数有限，且不支持自定义粗排及精排规则

    ```php
    /**
     * @param string $appName App名称
     * @param string $scrollID 上一次搜索结果中返回的scroll_id
     * @param string $expire 表示下一次 scroll请求的有效期，每次请求都必须设置该参数，可以用1m表示1min；支持的时间单位包括：w=Week, d=Day, h=Hour, m=minute, s=second
     * @param string $query 查询语句
     * @param integer $hits 返回文档的最大数量，无需设置start参数
     * @param null $filter 文档过滤条件
     * @param array $fetchFields 本次查询需要的字段内容
     * @param array $sort 文档排序规则，一维数组，需要设置排序字段(field)及排序规则(order)，排序规则可选值有 OpenSearch::SORT_INCREASE及OpenSearch::SORT_DECREASE
     * @return array
     */
    $openSearch->scroll($appName, $scrollID, $expire, $query, [$hits=10, $filter=null, $fetchFields=[], $sort=[]]);
    ```

7. [下拉提示](https://help.aliyun.com/document_detail/57156.html?spm=a2c4g.11186623.2.20.780373490wIdzu)

    ```php
    /**
     * @param string $appName App名称
     * @param string $suggestName 下拉提示名称
     * @param string $query 查询语句
     * @param int $hits 返回文档的最大数量
     * @return array
     */
    $openSearch->suggest($appName, $suggestName, $query, [$hits=10]);
    ```