<?php

/**
 * 退出登录
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\Commons;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class LogoutController extends Controller
{
    /**
     * 执行退出登录
     * */
    public function index(): object
    {
        DB::table('sys_login_logs')->where('id', $this->request->adminInfo['loginId'])->update(['logout_at'=>$this->getDatetime()]);
        Redis::del('token:'.$this->request->adminInfo['tokenId']);

        return response()->json($this->success('退出成功'));
    }
}