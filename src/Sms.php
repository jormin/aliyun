<?php

namespace Jormin\Aliyun;

use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;

/**
 * Class BaseObject
 * @package Jormin\Qiniu
 */
class Sms extends BaseObject {

    /**
     * Sms constructor.
     * @param $accessKeyId
     * @param $accessKeySecret
     */
    public function __construct($accessKeyId, $accessKeySecret)
    {
        parent::__construct($accessKeyId, $accessKeySecret);
        Config::load();
        $region = $endPointName = "cn-hangzhou";
        $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);
        DefaultProfile::addEndpoint($endPointName, $region, 'Dysmsapi', 'dysmsapi.aliyuncs.com');
        $this->client = new DefaultAcsClient($profile);
    }

    /**
     * 发送短信
     *
     * @param $phone
     * @param $signature
     * @param $templateCode
     * @param array $extData
     * @return array
     */
    public function sendSms($phone, $signature, $templateCode, $extData=[]) {
        if(!$phone || !$signature || !$templateCode){
            return $this->error('参数有误');
        }
        $request = new SendSmsRequest();
        $request->setPhoneNumbers($phone);
        $request->setSignName($signature);
        $request->setTemplateCode($templateCode);
        $request->setTemplateParam(json_encode($extData, JSON_UNESCAPED_UNICODE));
        $request->setOutId(time());
        return $this->parseResponse($request, '发送');
    }

    /**
     * 批量发送短信
     *
     * @param $phones
     * @param $signatures
     * @param $templateCode
     * @param array $extDatas
     * @return array
     */
    public function sendBatchSms($phones, $signatures, $templateCode, $extDatas=[]) {
        if(!count($phones) || !count($signatures) || !$templateCode){
            return $this->error('参数有误');
        }
        $request = new SendBatchSmsRequest();
        $request->setPhoneNumberJson(json_encode($phones, JSON_UNESCAPED_UNICODE));
        $request->setSignNameJson(json_encode($signatures, JSON_UNESCAPED_UNICODE));
        $request->setTemplateCode($templateCode);
        $request->setTemplateParamJson(json_encode($extDatas, JSON_UNESCAPED_UNICODE));
        return $this->parseResponse($request, '发送');
    }

    /**
     * 短信发送记录查询
     *
     * @param $phone
     * @param $sendDate
     * @param int $page
     * @param int $pageSize
     * @param string $bizId
     * @return array
     */
    public function querySendDetails($phone, $sendDate, $page=1, $pageSize=10, $bizId="") {
        if(!$phone || !$sendDate){
            return $this->error('参数有误');
        }
        $request = new QuerySendDetailsRequest();
        $request->setPhoneNumber($phone);
        $request->setSendDate($sendDate);
        $request->setPageSize($pageSize);
        $request->setCurrentPage($page);
        if($bizId){
            $request->setBizId($bizId);
        }
        return $this->parseResponse($request, '查询');
    }

    /**
     * 解析响应
     *
     * @param $request
     * @param $messagePrefix
     * @return array
     */
    protected function parseResponse($request, $messagePrefix){
        try{
            $response = $this->client->getAcsResponse($request);
            if(is_object($response)){
                $response = (array)$response;
            }
            if($response['Code'] === 'OK'){
                return $this->success($messagePrefix.'成功', $response);
            }else{
                return $this->error($response['Message'], $response);
            }
        }catch(\Exception $exception){
            return $this->error($messagePrefix.'失败', ['error' => $exception->getMessage()]);
        }
    }

}
