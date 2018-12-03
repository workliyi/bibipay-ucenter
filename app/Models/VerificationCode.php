<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationCode extends Model
{
    use Notifiable, SoftDeletes;

    /**
     * Get the notification routing information for the given driver.
     *
     * @return mixed
     */
    public function routeNotificationFor()
    {
        return $this->account;
    }

    /**
     * Has User.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     * @author Seven Du <shiweidu@outlook.com>
     */
    public function user()
    {
        return $this->hasOne(Users::class, 'id', 'user_id');
    }

    /**
     * 设置复用的创建时间范围查询，单位秒.
     *
     * @param Builder $query  查询对象
     * @param int     $second 范围时间，单位秒
     *
     * @return Builder 查询对象
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    public function scopeByValid(Builder $query, int $second = 300): Builder
    {
        $now = $this->freshTimestamp();
        $sub = clone $now;
        $sub->subSeconds($second);

        return $query->whereBetween('created_at', [$sub, $now]);
    }
    /**
     * 计算距离验证码过期时间.
     *
     * @param int $vaildSecond 验证的总时间
     *
     * @return int 剩余时间
     *
     * @author Seven Du <shiweidu@outlook.com>
     * @homepage http://medz.cn
     */
    public function makeSurplusSecond(int $vaildSecond = 60): int
    {
        $now = $this->freshTimestamp();
        $differ = $this->created_at->diffInSeconds($now);

        return $vaildSecond - $differ;
    }
    /**
     * 计算验证码过期时间
     */
    public function getCodeTime($phone,$status){
        $now = date('Y-m-d H:i:s',time());
        $sub = date('Y-m-d H:i:s',time()-60);
        $res = $this->where('account' , $phone)
                    ->where('state' , $status)
                    ->whereBetween('created_at' , [$sub , $now])
                    ->first();
        return $res;
    }
}
