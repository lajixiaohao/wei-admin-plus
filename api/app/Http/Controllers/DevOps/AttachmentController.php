<?php

/**
 * 附件管理
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\DevOps;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AttachmentController extends Controller
{
    /**
     * 附件列表
     * */
    public function index()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = ($page * $size) - $size;

        $keyword = $this->request->input('keyword');

        $where = [];
        if (! $this->request->adminInfo['isSuper']) {
            $where[] = ['a.admin_id', '=', $this->request->adminInfo['adminId']];
        }

        $type = $this->request->input('type');
        if ($type) {
            $where[] = ['a.type', '=', $type];
        }

        $list = DB::table('sys_file_uploads as a')
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
                $list[$k]->url = env('RESOURCE_URL').$v->url;
                unset($list[$k]->admin_id);
            }
            $count = DB::table('sys_file_uploads as a')
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