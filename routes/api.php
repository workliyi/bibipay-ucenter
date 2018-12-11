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
    //获取验证码接口
    $api->post('getcode' , 'APIs\UserSmsController@send');
    //用户认证接口
    $api->post('authentication' , 'APIs\UserController@setAuthentication');
    //获取用户认证信息
    $api->post('getauthentication' , 'APIs\UserController@getAuthentication');
    //修改用户认证状态
    $api->post('updauthentication' , 'APIs\UserController@updAuthentication');
    //设置用户图像
    $api->post('setuseravatar' , 'APIs\UserController@setUserAvatar');
    //设置用户图像
    $api->post('uptuseravatar' , 'APIs\UserController@uptUserAvatar');
    //获取用户图像
    $api->get('getuseravatar' , 'APIs\UserController@getUserAvatar');
    
});

//外部平台用户开放接口
Route::group(['middleware' => 'openapi'],function(RouteContract $api){
    $api->group(['prefix' => 'open'] , function(RouteContract $api){
        //获取用户信息接口
        $api->post('getuser' , 'APIs\UserController@GetUserDetail');
        //外部平台请求创建对应钱包接口
        $api->post('setwallet' , 'APIS\UserWalletController@CreateWallet');
        //外部平台获取对应钱包信息接口
        $api->post('walletdetail' , 'APIS\UserWalletController@GetWalletDetail');
        //外部平台获取所有钱包信息接口
        $api->post('allwalletdetail' , 'APIS\UserWalletController@GetAllWalletDetail');
        //外部平台操作用户钱包接口
        $api->post('modwallet' , 'APIS\UserWalletController@ModifyWallet');
        
        
    });
});
//外部平台操作开放接口
Route::group(['prefix' => 'openadmin'],function (RouteContract $api){
    //外部平台获取用户token接口
    $api->post('gettoken' , 'APIS\TokenController@getToken');
    //外部平台操作用户token接口
    $api->post('setToken' , 'APIS\TokenController@setToken');
    //外部平台操获取usdt总账数据
    $api->post('usdtdet' , 'APIS\TokenController@usdtDeteil');
    //提现申请审核列表
    $api->post('getapplylist' , 'APIS\WithDrawalsController@lists');
    //提现审核操作(通过/拒绝)
    $api->post('examine' , 'APIS\WithDrawalsController@examine');
});










