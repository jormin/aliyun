<?php
/**
 * Created by PhpStorm.
 * User: Jormin
 * Date: 2019-03-14
 * Time: 11:23
 */

namespace Jormin\Aliyun;

use Green\Request\V20180509\ImageSyncScanRequest;

class Yundun extends BaseObject
{

    /**
     * @var \DefaultAcsClient
     */
    protected $client;

    /**
     * @var string Endpoint
     */
    protected $endPoint;

    /**
     * Yundun constructor.
     * @param $regionId
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $endPoint
     */
    public function __construct($regionId, $accessKeyId, $accessKeySecret, $endPoint)
    {
        parent::__construct($accessKeyId, $accessKeySecret);
        $this->endPoint = $endPoint;
        $iClientProfile = \DefaultProfile::getProfile($regionId, $accessKeyId, $accessKeySecret);
        \DefaultProfile::addEndpoint($regionId, $regionId, 'Green', $endPoint);
        $this->client = new \DefaultAcsClient($iClientProfile);
    }

    /**
     * 图片审核
     * @param array $tasks
     * @param array $scenes
     * @return array
     */
    public function imageScan(array $tasks, array $scenes){
        $request = new ImageSyncScanRequest();
        $request->setMethod('POST');
        $request->setAcceptFormat('JSON');
        $data = [
            'tasks' => $tasks,
            'scenes' => $scenes
        ];
        $request->setContent(json_encode($data));
        try {
            $response = $this->client->getAcsResponse($request);
            if(200 !== intval($response->code)){
                return $this->error('调取阿里接口失败，'.$response->msg);
            }
            $responseData = $this->objectToArray($response->data);
            return $this->success('处理成功', $responseData);
        } catch (\Exception $exception) {
            return $this->error('调取阿里接口失败，'.$exception->getMessage());
        }
    }

}