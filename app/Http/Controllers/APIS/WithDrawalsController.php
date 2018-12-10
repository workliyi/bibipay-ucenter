<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19 0019
 * Time: 19:13
 */
namespace App\Http\Controllers\APIS;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Token as TokenModel;
use App\Models\WithDrawalsApply as WithdrawalsApplyModel;


class WithDrawalsController extends Controller
{
    //提现申请审核列表
    public function lists(Request $request, TokenModel $TokenModel ,WithdrawalsApplyModel $withdraw)
    {
        $data = $withdraw->leftJoin('withdrawals_apply.user_id' , 'user.id')->get();
    }

    //提现审核操作(通过/拒绝)
    public function examine(Request $request, WithdrawalsApplyModel $WithdrawalsApplyModel)
    {
        $data = $request->all();
        $result = $WithdrawalsApplyModel->where('id', $data['id'])->update(['status' => $data['status']]);
        $user = BaseUserModel::where('id', $WithdrawalsApplyModel->where('id', $data['id'])->value('user_id'))
            ->first();
        if ($result) {
            if ($data['status'] == 1 || $data['status'] == 2) {
//                $return = (new QclodeController())->send('尔尔', '15245161417', 202101);
                $return = (new QclodeController())->send($user['name'], $user['tel'], 202101);
                return $return;
            }
            if ($data['status'] == 3) {
//                $return = (new QclodeController())->send('王山', '15245161417', 202105);
                $return = (new QclodeController())->send($user['name'], $user['tel'], 206109);
                return $return;
            }
            return response()->json(['id' => $data['id'], 'code' => 200, 'message' => '成功']);
        }
    }
}