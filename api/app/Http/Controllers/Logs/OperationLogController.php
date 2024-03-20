<?php

/**
 * 操作日志模块
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OperationLogController extends Controller
{
    /**
     * 操作日志列表
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

        $keyword = $this->request->input('keyword', '');

        $list = DB::table('sys_operation_logs as a')
            ->join('sys_admins as b', 'a.admin_id', '=', 'b.id')
            ->where($where)
            ->where(function($query) use ($keyword) {
                if ($keyword) {
                    $query->where('b.account', 'like', '%'.$keyword.'%')->orWhere('b.nickname', 'like', '%'.$keyword.'%');
                }
            })
            ->select(DB::raw('a.id,a.uri,a.method,a.content,a.ipv4,a.user_agent,a.created_at,IFNULL(b.nickname,b.account) AS adminName'))
            ->offset($offset)
            ->limit($size)
            ->orderBy('a.id', 'desc')->get();
        $count = 0;
        if ($list->isNotEmpty()) {
            $count = DB::table('sys_operation_logs as a')
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