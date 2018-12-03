<?php

namespace App\Http\Controllers\APIS;


use App\Models\Users;
use App\Models\Platform;
use App\Models\VerificationCode;
use App\Models\AuthCodeKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class UserController extends Controller
{
    /**
     * @param Request $request
     * @param Users $users
     * @param UserAddress $userAddress
     * @param AuthCodeKey $authcode
     * @param ResponseFactoryContract $response
     * @return string
     *
     * 返回码
     *
     *      40031：平台不匹配
     */
    //对外登录
    public function UserLogin(Users $user , ResponseFactoryContract $response, AuthCodeKey $authcode, Request $request){
        $login = $request->input('phone');
        $user = Users::where('tel' , $login)->first();
        if(!$user){
            return $response->json(['message' => ['手机号还没有注册，请先注册']], 422);
        }
        session(['login' => $login]);
        $code = $request->input('verifiable_code');

        $verify = VerificationCode::where('account', $login)
            ->where('channel', 'sms')
            ->where('code', $code)
            ->orderby('id', 'desc')
            ->first();
        if (! $verify) {
            return $response->json(['message' => ['验证码过期或错误']], 422);
        }
        //$verify->delete();
        return $response->json([
            'base_token' =>$this->base_token($user->id , time()),
        ])->setStatusCode(201);
    }
    //用户注册
    public function UserRegister(Users $user,ResponseFactoryContract $response, Request $request){
        $phone = $request->input('phone');
        $email = $request->input('email');
        $name = $request->input('name');
        $password = $request->input('password');
        $channel = $request->input('verifiable_type');
        $code = $request->input('verifiable_code');
        $address = DB::table('user_address')->where('status', 0)->first();
        //判断用户是否已经注册
        $user = new Users();
        $isset_user = $user->where('tel' , $phone)->first();
        if($isset_user){
            return $response->json(['message' => '手机号已注册'], 422);
        }
        //判断是否有可用钱包地址
        if(!$address){
            return $response->json(['message' => '平台繁忙，请联系客服'], 422);
        }
        $verify = VerificationCode::where('account', $channel == 'mail' ? $email : $phone)
            ->where('channel', $channel)
            ->where('code', $code)
            ->where('state' , 2)
            ->orderby('id', 'desc')
            ->first();
        if (! $verify) {
            return $response->json(['message' => ['验证码错误或者已失效']], 422);
        }
        $key = md5(uniqid(md5(microtime(true)),true));
        //用户信息插入user表

        $user->tel = $phone;
        $user->email = $email;
        $user->name = $name;
        $user->created_at = time();
        $user->updated_at = time();
        $user->key = $key;
        $user->createPassword($password);
        $verify->delete();
        if (! $user->save()) {
            return $response->json(['message' => '注册失败'], 422);
        }
        /***
         *
         * 创建用户ipc钱包
         *
         **/
        //分配钱包地址

        $new_address['user_id'] = $user->id;
        $new_address['status'] = 1;
        DB::table('user_address')->where('id', $address->id)->update($new_address);
        //为新注册用户添加IPC钱包
        $new_wallet = [
            'user_id'          =>    $user->id,
            'type'             =>    1,
            'type_name'        =>    'IPC',
            'path'             =>    $address->ipc_path,
            'updated_at'       =>   date('Y-m-d H:i:s' , time())
        ];
        DB::table('token_wallet')->insert($new_wallet);
        return $response->json([
            'base_token' =>$this->base_token($user->id , time()),
        ])->setStatusCode(201);
    }
    //外部平台获取用户信息
    public function GetUserDetail(Platform $platform ,ResponseFactoryContract $response, Request $request,AuthCodeKey $authcode , Users $user){
        $plat = $platform->where('platform_num' , $request->platnum)->first();
        if($plat){
            $user_detail = $request->user;
            return $response->json($user_detail);
        } else {
            return 'code:40031';
        }
    }
    public function base_token($user_id,$time){
        $authcode = new AuthCodeKey();
        //获取用户key
        $user_key = Users::where('id' , $user_id)->first();
        $key = config('app.userkey');
        $time = base64_encode((string)$time);
        //加密的key
        $user_key = $user_key->key;
        // 加密
        $last_user_key = $authcode->authcode($user_key,'ENCODE',$key,0);
        //解密
        //$jiemi = $authcode->authcode($last_user_key,'DECODE',$key,0); //解密
        //设置的加密参数
        $data = "$user_key.$time";
        $hmac = hash_hmac("sha256", $data, $key, TRUE);
        //加密后字符串
        $signature = base64_encode($hmac);
        //最终生成的base_token
        $base_token = $last_user_key.'.'.$time.'.'.$signature;
        return $base_token;
    }
}

