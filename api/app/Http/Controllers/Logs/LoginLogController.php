<?php

/**
 * 登录日志模块
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LoginLogController extends Controller
{
    /**
     * 登录日志列表
     * */
    public function index()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = ($page * $size) - $size;

        $where = [];
        if (! $this->request->adminInfo['isSuper']) {
            $where[] = ['a.admin_id', '=', $this->request->adminInfo['adminId']];
        }

        $keyword = $this->request->input('keyword');

        $list = DB::table('sys_login_logs as a')
            ->join('sys_admins as b', 'a.admin_id', '=', 'b.id')
            ->where($where)
            ->where(function($query) use ($keyword) {
                if ($keyword) {
                    $query->where('b.account', 'like', '%'.$keyword.'%')->orWhere('b.nickname', 'like', '%'.$keyword.'%');
                }
            })
            ->select(DB::raw('a.*,IFNULL(b.nickname,b.account) AS adminName'))
            ->offset($offset)
            ->limit($size)
            ->orderBy('a.id', 'desc')->get();
        $count = 0;
        if ($list->isNotEmpty()) {
            foreach ($list as $k => $v) {
                $list[$k]->isCurrentDevice = $v->id === $this->request->adminInfo['loginId'];
                unset($list[$k]->admin_id);
            }
            $count = DB::table('sys_login_logs as a')
                ->join('sys_admins as b', 'a.admin_id', '=', 'b.id')
                ->where($where)
                ->where(function($query) use ($keyword) {
                    if ($keyword) {
                        $query->where('b.account', 'like', '%'.$keyword.'%')->orWhere('b.nickname', 'like', '%'.$keyword.'%');
                    }
                })->count();
        }

        return response()->json($this->success(['list'=>$list, 'count'=>$count]));
    }
}