<?php

namespace App\Http\Controllers\APIS;

use App\Models\Token;
use App\Models\UsdtDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Routing\ResponseFactory as ResponseFactoryContract;

class TokenController extends Controller
{
    /**
     * @param Request $request
     * @param Users $users
     * @return string
     * 返回码
     *      40031：平台不匹配
     */
    //获取token信息

    public function getToken(Token $token){
            $gettoken = $token->get();
            return $gettoken;
    }
    //修改手续费
    public function setToken(Request $request , Token $token,ResponseFactoryContract $response){
        $poundage = $request->poundage;
        $token_name = $request->token_name;
        $data = ['poundage' => $poundage];
        // return $poundage;
        $res = $token->where('token_name', $token_name)->update($data);
        if ($res){
            return $response->json(['code' => '200', 'message' => '修改成功']);
        } else {
            return $response->json(['code' => '40051' , 'message' => '修改失败']);
        }
    }
    //获取平台总账信息
    public function usdtDeteil(UsdtDetail $usdtModel){
        $usdtModel = $usdtModel->first();
        return $usdtModel;
}
}

