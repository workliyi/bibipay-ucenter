<?php

namespace App\Http\Controllers\APIS;

use App\Models\Users;
use App\Models\Token;
use App\Models\AuthCodeKey;
use App\Models\UserAddress;
use App\Models\QzChargeLog;
use App\Models\TokenWallet;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class UserWalletController extends Controller
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
     *      40021：缺少参数
     *      40022：不支持此类型token
     *      40023：钱包已存在
     *      40024: 钱包信息修改失败
     *      40024: 操作失败
     *      40024：操作钱包小于零
     *      20011: 钱包信息修改成功
     *
     */

    //创建用户钱包接口（对外开放接口）
    public function CreateWallet(Request $request , Users $users){
        $key = $request->key;
        $token_type = $request->tokentype;
        if ($key && $token_type){
            $token_detial = Token::select('id','token_name' , 'precision','poundage','status')->where('token_name' , $token_type)->first();
            $token_path = $this->get_path_type($token_detial->id);
            if ($token_detial){
                $user = $users->where('key' , $key)->first();
                $isset_wallet = TokenWallet::where('user_id' , $user->id)->where('type_name' , $token_type)->first();
                if (empty($isset_wallet)){
                    //分配钱包地址
                    $address = UserAddress::where('status', 0)->first();
                    $new_address['user_id'] = $user->id;
                    $new_address['status'] = 1;
                    DB::table('user_address')->where('id', $address->id)->update($new_address);
                    //为新注册用户添加对应钱包钱包
                    $new_wallet = [
                        'user_id'          =>    $user->id,
                        'type'             =>    $token_detial->id,
                        'type_name'        =>    $token_detial->token_name,
                        'path'             =>    $address->$token_path,
                        'updated_at'       =>   date('Y-m-d H:i:s' , time())
                    ];
                    DB::table('token_wallet')->insert($new_wallet);
                    return $token_detial;
                } else {
                    return 'code:40021';
                }
            } else {
                return 'code:40022';
            }
        } else {
            return 'code:40023';
        }
    }
    //获取用户中心钱包信息接口
    public function GetWalletDetail(Request $request , Users $users,ResponseFactoryContract $response){
        $user = $request->user;
        $key = $user->key;
        $token_type = $request->tokentype;
        $user = $users->where('key' , $key)->first();
        $isset_wallet = TokenWallet::where('user_id' , $user->id)->where('type_name' , $token_type)->first();
        $res_data = [
            'balance' =>$isset_wallet->balance,
            'total_income' => $isset_wallet->total_income,
            'total_expenses' => $isset_wallet->total_expenses,
            'token_type' =>$isset_wallet->type,
            'token_name' => $isset_wallet->type_name
        ];

        return $response->json($res_data);
    }
    //操作用户钱包接口
    public function ModifyWallet(Request $request,ResponseFactoryContract $response){
        $user = $request->user;
        $balance = $request->balance;
        $total_expenses = $request->total_expenses;
        $total_income = $request->total_income;
        $token_type = $request->tokentype;
        //记录用户钱包操作记录
        $charg_log_data = [
            'user_id' => $user->id,
            'less_number' => $request->less_number,
            'add_number' =>$request->add_number,
            'exe_status' =>$request->exe_status,
            'txid' =>$request->txid,
            'tid' =>$request->tid,
            'type' => $request->tokentype,
            'status' => $request->status,
            'created_time' => date('Y-m-d H:i:s' , time()),
            'action_type' => $request->action_type,
            'category' => $request->category
        ];
        $charg_log_data = array_filter($charg_log_data);
        //创建账户操作记录
        $charge_log = QzChargeLog::insertGetId($charg_log_data);
        if (!$charge_log){
            return $response->json(['message' => '操作失败','code' => '40024']);
        }
        $wallet_data = [
            'balance' => $balance,
            'total_expenses' => $total_expenses,
            'total_income' => $total_income,
        ];
        if($balance < 0){
            return $response->json(['message' => '操作失败','code' => '40025']);
        }
        $wallet_data = array_filter($wallet_data);
        $sta = TokenWallet::where('user_id' , $user->id)->where('type' , $token_type)->update($wallet_data);
        if($sta){
            return $response->json(['message' => '操作成功','code' => '20011']);
        } else {
            return $response->json(['message' => '操作失败','code' => '40024']);
        }
    }
    protected function get_path_type($type){
        switch ($type)
        {
            case 1:
                $path_name = 'ipc_path';
                break;
            case 2:
                $path_name = 'usdt_path';
                break;
            default:
                $path_name = 'ipc_path';
        }
        return $path_name;
    }
}
