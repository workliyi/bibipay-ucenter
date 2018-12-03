<?php

use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\Registrar as RouteContract;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


//用户相关操作接口
Route::group(['prefix' => 'users'],function (RouteContract $api){
    //用户登录接口
    $api->post('ulogin' , 'APIS\UserController@UserLogin');
    //用户注册接口
    $api->post('uregister' , 'APIS\UserController@UserRegister');
    //获取用户密钥接口
   // $api->post('getuser' , 'APIs\UserController@GetUserDetail');
    //获取验证码接口
    $api->post('getcode' , 'APIs\UserSmsController@send');
});
//修改用户密码接口
//修改用户支付密码接口

//外部平台开放接口
Route::group(['middleware' => 'openapi'],function(RouteContract $api){
    $api->group(['prefix' => 'open'] , function(RouteContract $api){
        //获取用户信息接口
        $api->post('getuser' , 'APIs\UserController@GetUserDetail');
        //外部平台请求创建对应钱包接口
        $api->post('setwallet' , 'APIS\UserWalletController@CreateWallet');
        //外部平台获取钱包信息接口
        $api->post('walletdetail' , 'APIS\UserWalletController@GetWalletDetail');
        //外部平台操作用户钱包接口
        $api->post('modwallet' , 'APIS\UserWalletController@ModifyWallet');
    });
});