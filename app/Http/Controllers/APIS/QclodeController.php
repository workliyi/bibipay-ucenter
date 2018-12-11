<?php
namespace App\Http\Controllers\APIS;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Qcloud\Sms\SmsSingleSender;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseContract;

class QclodeController extends Controller
{
    //短信入库模型
    protected $smsdata;
    //发送申请提现审核结果通知短信
    public function send($username ,$phone, $qid)
    {
        $smsSign ='bibipay';
        $appid = '1400136004';
        $appkey ='dfba8fe3d16454f28a492317eec91a06';
        $ssender = new SmsSingleSender($appid, $appkey);
        $para = [$username];
        $result = $ssender->sendWithParam("86", $phone, $qid, $para, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
        $rsp = json_decode($result);
        return $result;
    }
  
  
  //发送账号认证驳回短信
    public function revoke($username ,$phone, $qid, $reason)
    {
        $smsSign ='bibipay';
        $appid = '1400136004';
        $appkey ='dfba8fe3d16454f28a492317eec91a06';
        $ssender = new SmsSingleSender($appid, $appkey);
        $para = [$username, $reason];
        $result = $ssender->sendWithParam("86", $phone, $qid, $para, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
        $rsp = json_decode($result);
        return $result;
    }
}