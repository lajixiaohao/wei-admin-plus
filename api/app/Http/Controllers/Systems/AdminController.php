<?php

/**
 * 管理员管理
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\Systems;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Helps\Validate;
use App\Helps\RsaSecret;

class AdminController extends Controller
{
    /**
     * 管理员列表
     * */
    public function index()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = ($page * $size) - $size;

        $keyword = $this->request->input('keyword', '');

        $list = DB::table('sys_admins as a')
            ->leftJoin('sys_roles as b', 'a.role_id', '=', 'b.id')
            ->where('a.parent_id', $this->request->adminInfo['adminId'])
            ->where(function($query) use ($keyword) {
                if ($keyword) {
                    $query->where('a.account', 'like', '%'.$keyword.'%')->orWhere('a.nickname', 'like', '%'.$keyword.'%');
                }
            })
            ->select(['a.id','a.account','a.nickname','a.role_id','a.is_able','a.created_at','a.updated_at','b.role_name'])
            ->offset($offset)
            ->limit($size)
            ->orderBy('a.id', 'desc')->get();
        $count = 0;
        if ($list->isNotEmpty()) {
            foreach ($list as $k => $v) {
                $list[$k]->childrenNum = DB::table('sys_admins')->where('parent_id', $v->id)->count();
            }
            $count = DB::table('sys_admins as a')
                ->leftJoin('sys_roles as b', 'a.role_id', '=', 'b.id')
                ->where('a.parent_id', $this->request->adminInfo['adminId'])
                ->where(function($query) use ($keyword) {
                    if ($keyword) {
                        $query->where('a.account', 'like', '%'.$keyword.'%')->orWhere('a.nickname', 'like', '%'.$keyword.'%');
                    }
                })->count();
        }

        return response()->json($this->success(['list'=>$list, 'count'=>$count]));
    }

    /**
     * 添加
     * */
    public function add()
    {
        // 初始化
        if ($this->request->input('init') == 1) {
            return response()->json($this->success(['roles'=>$this->_getAssignableRoles()]));
        }

        $validate = new Validate();

        // 账号
        $account = $this->request->input('account');
        if (! $validate->isValidAccount($account)) {
            return response()->json($this->fail('账号输入有误'));
        }

        // 获取私钥
        $privatekey = $this->getPrivateKey($this->request->adminInfo['tokenId']);
        if (! $privatekey) {
            return response()->json($this->fail('系统异常，请稍后重试'));
        }

        // 密码
        $pwd = (new RsaSecret())->decryptPassword($this->request->input('pwd', ''), $privatekey);
        if (! $validate->isValidPassword($pwd)) {
            return response()->json($this->fail('密码输入有误'));
        }

        if (DB::table('sys_admins')->where('account', $account)->exists()) {
            return response()->json($this->fail('该账号已被占用'));
        }

        // 角色id
        $role_id = $this->request->input('role_id');
        if ($role_id && ! $this->_isMyChildRole($role_id)) {
            return response()->json($this->fail('非法操作'));
        }

        $id = DB::table('sys_admins')->insertGetId([
            'parent_id'=>$this->request->adminInfo['adminId'],
            'account'=>$account,
            'nickname'=>$this->request->input('nickname'),
            'pwd'=>password_hash($pwd, PASSWORD_DEFAULT),
            'role_id'=>$role_id,
            'is_able'=>$this->request->input('is_able'),
            'created_at'=>$this->getDatetime(),
            'updated_at'=>$this->getDatetime()
        ]);
        if ($id <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordOperationLog("添加管理员(id={$id})");

        return response()->json($this->success('添加成功'));
    }

    /**
     * 编辑
     * */
    public function edit()
    {
        // 初始化
        if ($this->request->input('init') == 1) {
            return response()->json($this->success(['roles'=>$this->_getAssignableRoles()]));
        }

        // 管理员id
        $id = $this->request->input('id');

        if (! $this->_isMyChildAdmin($id)) {
            return response()->json($this->fail('非法操作'));
        }

        // 角色id
        $role_id = $this->request->input('role_id');
        if ($role_id && ! $this->_isMyChildRole($role_id)) {
            return response()->json($this->fail('非法操作'));
        }

        $res = DB::table('sys_admins')->where('id', $id)->update([
            'nickname'=>$this->request->input('nickname'),
            'role_id'=>$role_id,
            'is_able'=>$this->request->input('is_able'),
            'updated_at'=>$this->getDatetime()
        ]);
        if ($res === false) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordOperationLog("编辑管理员(id={$id})");

        return response()->json($this->success('编辑成功'));
    }

    /**
     * 添加或编辑前获取可分配角色
     * */
    private function _getAssignableRoles()
    {
        $where = [
            ['parent_id', '=', $this->request->adminInfo['roleId']],
            ['is_able', '=', 1]
        ];
        return DB::table('sys_roles')->where($where)->select('id', 'role_name')->orderBy('id', 'desc')->get();
    }

    /**
     * 重置密码
     * */
    public function resetPassword()
    {
        // 管理员id
        $id = $this->request->input('id');

        if (! $this->_isMyChildAdmin($id)) {
            return response()->json($this->fail('非法操作'));
        }

        // 获取私钥
        $privatekey = $this->getPrivateKey($this->request->adminInfo['tokenId']);
        if (! $privatekey) {
            return response()->json($this->fail('系统异常，请稍后重试'));
        }

        $pwd = (new RsaSecret())->decryptPassword($this->request->input('pwd', ''), $privatekey);
        if (! (new Validate())->isValidPassword($pwd)) {
            return response()->json($this->fail('密码输入有误'));
        }

        $res = DB::table('sys_admins')
            ->where('id', $id)
            ->update([
                'pwd'=>password_hash($pwd, PASSWORD_DEFAULT),
                'updated_at'=>$this->getDatetime()
            ]);
        if (! $res) {
            return response()->json($this->fail('重置失败'));
        }

        $this->recordOperationLog("重置管理员(id={$id})密码");

        return response()->json($this->success('重置成功'));
    }

    /**
     * 是否我的下级管理员
     * */
    private function _isMyChildAdmin(int $id): bool
    {
        $where = [
            ['id','=',$id],
            ['parent_id','=',$this->request->adminInfo['adminId']]
        ];
        return DB::table('sys_admins')->where($where)->exists();
    }

    /**
     * 是否我的下级角色
     * */
    private function _isMyChildRole(int $id): bool
    {
        $where = [
            ['id','=',$id],
            ['parent_id','=',$this->request->adminInfo['roleId']]
        ];
        return DB::table('sys_roles')->where($where)->exists();
    }

    /**
     * 预览下级
     * */
    public function viewChildren(int $id)
    {
        if ($id <= 0) {
            $id = $this->request->adminInfo['adminId'];
        }

        $data = DB::table('sys_admins')
            ->where('parent_id', $id)
            ->select('id','account','nickname')
            ->orderBy('id', 'DESC')->get();
        if ($data->isNotEmpty()) {
            foreach ($data as $k => $v) {
                $data[$k]->leaf = ! DB::table('sys_admins')->where('parent_id', $v->id)->select('id','account','nickname')->exists();
            }
        }

        return response()->json($this->success(['list'=>$data]));
    }

    /**
     * 删除
     * */
    public function delete(int $id)
    {
        $where = [
            ['id','=',$id],
            ['parent_id','=',$this->request->adminInfo['adminId']]
        ];
        $account = DB::table('sys_admins')->where($where)->value('account');
        if (! $account) {
            return response()->json($this->fail('非法操作'));
        }

        // 若存在下级，则不能删除
        if (DB::table('sys_admins')->where('parent_id', $id)->exists()) {
            return response()->json($this->fail('删除失败：该管理员还有子管理员'));
        }

        if (! DB::table('sys_admins')->where('id', $id)->delete()) {
            return response()->json($this->fail('删除失败'));
        }

        $this->recordOperationLog('删除管理员，账号：'.$account);
        
        return response()->json($this->success('删除成功'));
    }
}