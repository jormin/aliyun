<?php

namespace Jormin\Aliyun;

use Aliyun\Api\Sms\Request\V20170525\QuerySendDetailsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

include_once dirname(__FILE__).'/../sdk/aliyun-dysms-php-sdk/api_sdk/vendor/autoload.php';

/**
 * Class BaseObject
 * @package Jormin\Qiniu
 */
class Sms extends BaseObject {

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
        if(!$phone || !$signature || $templateCode){
            return $this->error('参数有误');
        }
        $request = new SendSmsRequest();
        $request->setPhoneNumbers($phone);
        $request->setSignName($signature);
        $request->setTemplateCode($$templateCode);
        $request->setTemplateParam(json_encode($extData, JSON_UNESCAPED_UNICODE));
        $request->setOutId(time());
        try{
            $response = $this->client->getAcsResponse($request);
            return $this->success('发送成功', $response);
        }catch(\Exception $exception){
            return $this->error('发送失败', ['error' => $exception->getMessage()]);
        }
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
        if(!count($phones) || !count($signatures) || $templateCode){
            return $this->error('参数有误');
        }
        $request = new SendBatchSmsRequest();
        $request->setPhoneNumberJson(json_encode($phones, JSON_UNESCAPED_UNICODE));
        $request->setSignNameJson(json_encode($signatures, JSON_UNESCAPED_UNICODE));
        $request->setTemplateCode($templateCode);
        $request->setTemplateParamJson(json_encode($extDatas, JSON_UNESCAPED_UNICODE));
        try{
            $response = $this->client->getAcsResponse($request);
            return $this->success('发送成功', $response);
        }catch(\Exception $exception){
            return $this->error('发送失败', ['error' => $exception->getMessage()]);
        }
    }

    /**
     * 短信发送记录查询
     *
     * @param $phone
     * @param $sendDate
     * @param int $page
     * @param int $pageSize
     * @param string $BizId
     * @return array
     */
    public function querySendDetails($phone, $sendDate, $page=1, $pageSize=10, $BizId="") {
        if(!$phone || $sendDate){
            return $this->error('参数有误');
        }
        $request = new QuerySendDetailsRequest();
        $request->setPhoneNumber($phone);
        $request->setSendDate($sendDate);
        $request->setPageSize($pageSize);
        $request->setCurrentPage($page);
        if($BizId){
            $request->setBizId($BizId);
        }
        try{
            $response = $this->client->getAcsResponse($request);
            return $this->success('发送成功', $response);
        }catch(\Exception $exception){
            return $this->error('发送失败', ['error' => $exception->getMessage()]);
        }
    }

}
