<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Users;
use App\Models\Platform;
use App\Models\AuthCodeKey;

class OpenApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next , $massage = '')
    {
        $data = $request->all();
        if (!empty($data['platkey'] && !empty($data['platnum']))){
            $authcode = new AuthCodeKey();
            $plat = Platform::where('platform_num' , $data['platnum'])->first();
            $res = $authcode->authcode($data['platkey'],'DECODE',$plat->key,0);//解密
            $users = ['user' => Users::where('key' , $res)->first()];
            if ($users){
                $request->merge($users);//添加参数
                return $next($request);
            } else {
                return redirect('/home');
            }
        } else {
            return redirect('/home');
        }
    }
}
