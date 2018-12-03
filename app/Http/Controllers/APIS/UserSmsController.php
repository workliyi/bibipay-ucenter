<?php

namespace App\Http\Controllers\APIS;

use App\Models\Users;
use Illuminate\Http\Request;
use Qcloud\Sms\SmsSingleSender;
use App\Models\VerificationCode;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseContract;

class UserSmsController extends Controller
{
    //验证码数字串
    protected $num = '1234567890';
    public function send(Request $request , VerificationCode $verif,ResponseContract $response)
    {
        $status = $request->status;
        if($status == 1){
            $user = Users::where('phone' , $request->phone)->first();
            if(!$user){
                return $response->json(['message' => ['手机号还没有注册，请先注册']], 422);
            }
        }
        $phoneNumbers = $request->phone;
        $codeTime =  $verif->getCodeTime($phoneNumbers,$status);
        if(!empty($codeTime)){
            return $response->json(['message' => ['请一分钟之后再获取验证码']], 422);
        }
        $templateId = config('qcsms.templateid');
        $smsSign =config('qcsms.smssign');
        $appid = config('qcsms.app_id');
        $appkey =config('qcsms.app_key');
        $params=array(substr(str_shuffle($this->num) , 0 , 6));
        $ssender = new SmsSingleSender($appid, $appkey);
        $result = $ssender->sendWithParam("86", $phoneNumbers, $templateId,
            $params, $smsSign, "", "");  // 签名参数未提供或者为空时，会使用默认签名发送短信
        $rsp = json_decode($result);
        if($rsp->result == 0){
            $verif->account = $phoneNumbers;
            $verif->channel = 'sms';
            $verif->code = $params[0];
            $verif->state = $status;
            $verif->save();
            return $response->json(['message' => '验证码发送成功' , 'code' => '200']);
        } else {
            return $response->json(['message' => '验证码发送失败' , 'code' => '501']);
        }
    }
}
