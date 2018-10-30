<?php

namespace Jormin\Aliyun;

include_once '../sdk/aliyun-opensearch-php-sdk/OpenSearch/Autoloader/Autoloader.php';
use OpenSearch\Client\AppClient;
use OpenSearch\Client\DocumentClient;
use OpenSearch\Client\OpenSearchClient;
use OpenSearch\Client\SearchClient;
use OpenSearch\Client\SuggestClient;
use OpenSearch\Generated\Common\Pageable;
use OpenSearch\Util\SearchParamsBuilder;
use OpenSearch\Util\SuggestParamsBuilder;

/**
 * Class OpenSearch
 * @package Jormin\Aliyun
 */
class OpenSearch extends BaseObject {


    const SORT_INCREASE = 1;
    const SORT_DECREASE = 0;

    /**
     * @var string 对应区域api访问地址，可参考应用控制台,基本信息中api地址
     */
    protected $host;

    /**
     * @var bool 是否调试模式
     */
    protected $debug;

    /**
     * OpenSearch constructor.
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $host
     * @param bool $debug
     */
    public function __construct($accessKeyId, $accessKeySecret, $host, $debug=false)
    {
        parent::__construct($accessKeyId, $accessKeySecret);
        $this->host = $host;
        $this->debug = $debug;
        $options = ['debug'=>$this->debug];
        $this->client = new OpenSearchClient($this->accessKeyId, $this->accessKeySecret, $this->host, $options);
    }

    /**
     * 获取App列表
     * @param int $page 获取第几页应用列表，该参数值必须大于0，否者会报错
     * @param int $size 每页返回的应用个数，该参数值必须大于或等于0，否者会报错
     * @return array
     */
    public function getApps($page=1, $size=10) {
        $pageable = new Pageable(['page'=>$page, 'size'=>$size]);
        $appClient = new AppClient($this->client);
        $response = $appClient->listAll($pageable);
        return $this->parseResponse($response, '获取App列表');
    }

    /**
     * 获取App信息
     * @param string $appName App名称
     * @return array
     */
    public function getAppInfo($appName) {
        $appClient = new AppClient($this->client);
        $response = $appClient->getById($appName);
        return $this->parseResponse($response, '获取App信息');
    }

    /**
     * 上传文档
     * @param string $appName App名称
     * @param string $tableName 表名称
     * @param array $data 推送二维数据，每个元素包含cmd和fields两个字段，其中fields字段是个数组，标准版仅支持ADD和DELETE操作，高级版额外支持UPDATE操作
     * @return array
     */
    public function pushDoc($appName, $tableName, $data) {
        if(!$appName || !$tableName || !$data || !is_array($data)){
            return $this->error('参数有误');
        }
        $documentClient = new DocumentClient($this->client);
        $response = $documentClient->push(json_encode($data), $appName, $tableName);
        return $this->parseResponse($response, '推送文档');
    }

    /**
     * 搜索
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
    public function search($appName, $query, $firstRankName='default', $secondRankName='default', $start=0, $hits=10, $filter=null, $kvpairs=null, $qp=[], $fetchFields=[], $sort=[], $distinct=[], $agregate=[], $disable=[], $summary=[]){
        if(!$appName || !$query){
            return $this->error('参数有误');
        }
        $searchClient = new SearchClient($this->client);
        $params = new SearchParamsBuilder();
        $params->setFormat('fulljson');
        $params->setAppName($appName);
        $params->setQuery($query);
        $params->setStart($start);
        $params->setHits($hits);
        $params->setFirstRankName($firstRankName);
        $params->setSecondRankName($secondRankName);
        if(count($qp)){
            foreach ($qp as $qpItem){
                $params->addQueryProcessor($qpItem);
            }
        }
        if($kvpairs){
            $params->setKvPairs($kvpairs);
        }
        if($filter){
            $params->setFilter($filter);
        }
        if(count($sort)){
            foreach ($sort as $sortItem){
                $params->addSort($sortItem['field'], $sortItem['order']);
            }
        }
        if(count($distinct)){
            foreach ($distinct as $distinctItem){
                $params->addDistinct($distinctItem);
            }
        }
        if(count($disable)){
            foreach ($disable as $disableItem){
                $params->addDisableFunctions($disableItem);
            }
        }
        if(count($agregate)){
            foreach ($agregate as $agregateItem){
                $params->addAggregate($agregateItem);
            }
        }
        if(count($fetchFields)){
            $params->setFetchFields($fetchFields);
        }
        if(count($summary)){
            foreach ($summary as $summaryItem){
                $params->addSummary($summaryItem);
            }
        }
        $response = $searchClient->execute($params->build());
        return $this->parseResponse($response, '搜索文档');
    }

    /**
     * 扫描
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
    public function scroll($appName, $scrollID, $expire, $query, $hits=10, $filter=null, $fetchFields=[], $sort=[]){
        if(!$appName || !$query || !$expire){
            return $this->error('参数有误');
        }
        $searchClient = new SearchClient($this->client);
        $params = new SearchParamsBuilder();
        $params->setFormat('fulljson');
        $params->setAppName($appName);
        $params->setScrollId($scrollID);
        $params->setScrollExpire($expire);
        $params->setQuery($query);
        $params->setHits($hits);
        if($filter){
            $params->setFilter($filter);
        }
        if(count($sort)){
            foreach ($sort as $sortItem){
                $params->addSort($sortItem['field'], $sortItem['order']);
            }
        }
        if(count($fetchFields)){
            $params->setFetchFields($fetchFields);
        }
        $response = $searchClient->execute($params->build());
        return $this->parseResponse($response, '搜索文档');
    }

    /**
     * 下拉提示
     * @param string $appName App名称
     * @param string $suggestName 下拉提示名称
     * @param string $query 查询语句
     * @param int $hits 返回文档的最大数量
     * @return array
     */
    public function suggest($appName, $suggestName, $query, $hits=10){
        if(!$appName || !$suggestName || !$query){
            return $this->error('参数有误');
        }
        $suggestClient = new SuggestClient($this->client);
        $params = SuggestParamsBuilder::build($appName, $suggestName, $query, $hits);
        $response = $suggestClient->execute($params);
        return $this->parseResponse($response, '获取下拉提示');
    }

    /**
     * 解析响应
     * @param $response
     * @param $messagePrefix
     * @return array
     */
    protected function parseResponse($response, $messagePrefix){
        $response = json_decode($response->result, true);
        if((isset($response['status']) && $response['status'] === 'OK') || (!isset($response['status']) && !isset($response['errors'])) || (isset($response['errors']) && count($response['errors']) === 0)){
            return $this->success($messagePrefix.'成功', $response);
        }else{
            return $this->error($messagePrefix.'失败', $response);
        }
    }

}
