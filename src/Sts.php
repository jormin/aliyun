<?php
/**
 * Created by PhpStorm.
 * User: Jormin
 * Date: 2019-03-14
 * Time: 11:23
 */

namespace Jormin\Aliyun;


use Sts\Request\V20150401\AssumeRoleRequest;

class Sts extends BaseObject
{

    /**
     * @var \DefaultAcsClient
     */
    protected $client;

    /**
     * Yundun constructor.
     * @param $regionId
     * @param $accessKeyId
     * @param $accessKeySecret
     */
    public function __construct($regionId, $accessKeyId, $accessKeySecret)
    {
        parent::__construct($accessKeyId, $accessKeySecret);
        $iClientProfile = \DefaultProfile::getProfile($regionId, $accessKeyId, $accessKeySecret);
        $this->client = new \DefaultAcsClient($iClientProfile);
    }

    /**
     * 获取Token
     * @param string $roleSessionName
     * @param string $roleArn
     * @param string $policy
     * @param int $tokenExpire
     * @return array
     */
    public function getToken(string $roleSessionName, string $roleArn, string $policy, int $tokenExpire)
    {
        $request = new AssumeRoleRequest();
        $request->setRoleSessionName($roleSessionName);
        $request->setRoleArn($roleArn);
        $request->setPolicy($policy);
        $request->setDurationSeconds($tokenExpire);
        try {
            $response = $this->client->getAcsResponse($request);
            return $this->success('处理成功', $this->objectToArray($response));
        } catch(\Exception $exception) {
            return $this->error('调取阿里接口失败，'.$exception->getMessage());
        }
    }

}