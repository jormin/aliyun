<?php

namespace Jormin\Aliyun;

use \Push\Request\V20160801 as AliyunPush;

/**
 * Class Push
 * @package Jormin\Aliyun
 */
class Push extends BaseObject {

    public static $PLATFORM_ANDROID = 1;
    public static $PLATFORM_IOS = 2;

    public static $TARGET_DEVICE = 'DEVICE';
    public static $TARGET_ACCOUNT = 'ACCOUNT';
    public static $TARGET_ALIAS = 'ALIAS';
    public static $TARGET_TAG = 'TAG';
    public static $TARGET_ALL = 'ALL';

    public static $PUSHTYPE_MESSAGE = 'MESSAGE';
    public static $PUSHTYPE_NOTICE = 'NOTICE';

    protected $androidAppKey, $iosAppkey;

    /**
     * Push constructor.
     *
     * @param $accessKeyId
     * @param $accessKeySecret
     * @param $androidAppKey
     * @param $iosAppkey
     */
    public function __construct($accessKeyId, $accessKeySecret, $androidAppKey, $iosAppkey)
    {
        parent::__construct($accessKeyId, $accessKeySecret);
        $this->androidAppKey = $androidAppKey;
        $this->iosAppkey = $iosAppkey;
    }

    /**
     * 推送
     *
     * @param Integer $platform 推送平台 0:全部 1:Android 2:IO
     * @param String $target 推送目标 DEVICE:根据设备推送 ACCOUNT:根据账号推送 ALIAS:根据别名推送 TAG:根据标签推送 ALL:推送给全部设备(同一种DeviceType的两次全推的间隔至少为1秒)
     * @param String $targetValue Target值
     * @param String $pushType 推送类型 MESSAGE:消息 NOTICE:通知
     * @param String $title 标题
     * @param String $body 内容
     * @param array $extData 额外数据
     * @param array $commonConfig 通用配置数据
     * @param array $androidConfig 安卓配置数据
     * @param array $iosConfig IOS配置数据
     * @return array
     */
    public function push($platform, $target, $targetValue, $pushType, $title, $body, $extData=[], $commonConfig = [], $androidConfig=[], $iosConfig=[])
    {
        if(!in_array($platform, [1, 2])){
            return $this->error('推送平台错误');
        }
        if(!in_array($target, ['DEVICE', 'ACCOUNT', 'ALIAS', 'TAG', 'ALL'])){
            return $this->error('推送目标错误');
        }
        if(!in_array($pushType, ['MESSAGE', 'NOTICE'])){
            return $this->error('推送方式错误');
        }
        if($extData){
            $extData = json_encode($extData);
        }
        $request = new AliyunPush\PushRequest();
        $request->setTarget($target);
        if($target == self::$TARGET_ALL){
            $request->setTargetValue('ALL');
        }else{
            $request->setTargetValue($targetValue);
        }
        $request->setPushType($pushType);
        $request->setTitle($title);
        $request->setBody($body);
        foreach ($commonConfig as $key => $value){
            $method = 'set'.$key;
            if(!is_null($value)){
                $request->$method($value);
            }
        }
        switch ($platform){
            case self::$PLATFORM_ANDROID:
                $request->setAppKey($this->androidAppKey);
                $request->setDeviceType("ANDROID");
                $request->setAndroidNotificationChannel(1);
                $request->setStoreOffline(true);
                if($extData){
                    $request->setAndroidExtParameters($extData);
                }
                foreach ($androidConfig as $key => $value){
                    $method = 'set'.$key;
                    if(!is_null($value)){
                        $request->$method($value);
                    }
                }
                break;
            case self::$PLATFORM_IOS:
                $request->setAppKey($this->iosAppkey);
                $request->setDeviceType("iOS");
                if($extData){
                    $request->setiOSExtParameters($extData);
                }
                foreach ($iosConfig as $key => $value){
                    $method = 'set'.$key;
                    if(!is_null($value)){
                        $request->$method($value);
                    }
                }
                break;
        }
        try{
            $response = (array)$this->client->getAcsResponse($request);
            return $this->success('推送成功', $response);
        }catch(\Exception $exception){
            return $this->error('推送失败', ['error' => $exception->getMessage()]);
        }
    }
}
