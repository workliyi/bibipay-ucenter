<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/19 0019
 * Time: 19:13
 */
namespace App\Http\Controllers\APIS;

use App\Models\Users;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\APIS\QclodeController;
use App\Models\Token as TokenModel;
use App\Models\WithDrawApply as WithdrawalsApplyModel;


class WithDrawalsController extends Controller
{
    //提现申请审核列表
    public function lists(Request $request, TokenModel $TokenModel ,WithdrawalsApplyModel $withdraw)
    {
        $token_symbol = $request->token_symbol;
        $status = $request->status;
        if(!empty($token_symbol) && is_null($status)){
            $data = $withdraw->select('withdrawals_apply.id','token.token_name','withdrawals_apply.address',
            'withdrawals_apply.balance','token.poundage', 'token.status',
            'withdrawals_apply.status','withdrawals_apply.created_time')->where('token.id' , $token_symbol)->leftJoin('token','token.id' , '=' , 'withdrawals_apply.type')->paginate(15);
        } elseif(empty($token_symbol) && !is_null($status)){
            $data = $withdraw->select('withdrawals_apply.id','token.token_name','withdrawals_apply.address',
            'withdrawals_apply.balance','token.poundage', 'token.status',
            'withdrawals_apply.status','withdrawals_apply.created_time')->where('withdrawals_apply.status' , $status)->leftJoin('token','token.id' , '=' , 'withdrawals_apply.type')->paginate(15);
        } elseif(!empty($token_symbol) && !is_null($status)) {
            $data = $withdraw->select('withdrawals_apply.id','token.token_name','withdrawals_apply.address',
            'withdrawals_apply.balance','token.poundage', 'token.status',
            'withdrawals_apply.status','withdrawals_apply.created_time')->where('token.id' , $token_symbol)
            ->where('withdrawals_apply.status' , $status)->leftJoin('token','token.id' , '=' , 'withdrawals_apply.type')->paginate(15);
        } else {
            $data = $withdraw->select('withdrawals_apply.id','token.token_name','withdrawals_apply.address',
            'withdrawals_apply.balance','token.poundage', 'token.status',
            'withdrawals_apply.status','withdrawals_apply.created_time')->leftJoin('token','token.id' , '=' , 'withdrawals_apply.type')->paginate(15);
        
        }
        $poundage = $TokenModel->select('id', 'token_name', 'poundage')->get();
        return response()->json([
            'data' => $data,
            'poundage' => $poundage,
        ]);
    }

    //提现审核操作(通过/拒绝)
    public function examine(Request $request, WithdrawalsApplyModel $WithdrawalsApplyModel)
    {
        $id = $request->id;
        $status = $request->status;
        $user = Users::where('id', $WithdrawalsApplyModel->where('id', $id)->value('user_id'))
            ->first();
        if(empty($user)){
            return $response->json(['code' => '422', 'message' => '参数错误']);
        }
        $result = $WithdrawalsApplyModel->where('id', $id)->update(['status' => $status]);
        if ($result) {
            if ($status == 1 || $status == 2) {
                $return = (new QclodeController())->send($user['name'], $user['tel'], 202101);
                return $return;
            }
            if ($status == 3) {
//                $return = (new QclodeController())->send('王山', '15245161417', 202105);
                $return = (new QclodeController())->send($user['name'], $user['tel'], 206109);
                return $return;
            }
            return response()->json(['id' => $id, 'code' => 200, 'message' => '成功']);
        }
    }
}