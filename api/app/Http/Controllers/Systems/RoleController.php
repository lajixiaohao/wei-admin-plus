<?php

/**
 * 角色管理
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\Systems;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * 角色列表
     * */
    public function index()
    {
        $page = $this->request->input('page', 1);
        $size = $this->request->input('size', 10);
        $offset = ($page * $size) - $size;

        $where = [['parent_id','=',$this->request->adminInfo['roleId']]];

        $role_name = $this->request->input('role_name');
        if ($role_name) {
            $where[] = ['role_name', 'like', '%'.$role_name.'%'];
        }

        $list = DB::table('sys_roles')
            ->where($where)
            ->select(['id','role_name','introduce','menu_ids','is_able','created_at','updated_at'])
            ->offset($offset)
            ->limit($size)
            ->orderBy('id', 'desc')->get();
        $count = 0;
        if ($list->isNotEmpty()) {
            foreach ($list as $k => $v) {
                $list[$k]->adminNums = DB::table('sys_admins')->where('role_id', $v->id)->count();
                $list[$k]->childrenNums = DB::table('sys_roles')->where('parent_id', $v->id)->count();
                $list[$k]->authNums = $v->menu_ids ? count(explode(',', $v->menu_ids)) : 0;
                unset($list[$k]->menu_ids);
            }
            $count = DB::table('sys_roles')->where($where)->count();
        }

        return response()->json($this->success(['list'=>$list, 'count'=>$count]));
    }

    /**
     * 添加
     * */
    public function add()
    {
        $role_name = $this->request->input('role_name');
        if (! $role_name) {
            return response()->json($this->fail('请输入角色名称'));
        }

        // 同级下不能重复
        $where = [
            ['role_name','=',$role_name],
            ['parent_id','=',$this->request->adminInfo['roleId']]                 
        ];
        if (DB::table('sys_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色名称已存在'));
        }

        $id = DB::table('sys_roles')->insertGetId([
            'parent_id'=>$this->request->adminInfo['roleId'],
            'role_name'=>$role_name,
            'introduce'=>$this->request->input('introduce'),
            'is_able'=>$this->request->input('is_able', 1),
            'created_at'=>$this->getDatetime(),
            'updated_at'=>$this->getDatetime()
        ]);

        if ($id <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        $this->recordOperationLog("添加角色(id={$id})");

        return response()->json($this->success('添加成功'));
    }

    /**
     * 编辑
     * */
    public function edit()
    {
        // 角色id
        $id = $this->request->input('id');

        if (! $this->_isMyChildRole($id)) {
            return response()->json($this->fail('非法操作'));
        }

        $role_name = $this->request->input('role_name');
        if (! $role_name) {
            return response()->json($this->fail('请输入角色名称'));
        }

        // 同级下不能重复
        $where = [
            ['id','<>',$id],
            ['role_name','=',$role_name],
            ['parent_id','=',$this->request->adminInfo['roleId']]
        ];
        if (DB::table('sys_roles')->where($where)->exists()) {
            return response()->json($this->fail('该角色名称已存在'));
        }

        $res = DB::table('sys_roles')->where('id', $id)->update([
            'role_name'=>$role_name,
            'introduce'=>$this->request->input('introduce'),
            'is_able'=>$this->request->input('is_able'),
            'updated_at'=>$this->getDatetime()
        ]);

        if ($res === false) {
            return response()->json($this->fail('编辑失败'));
        }

        $this->recordOperationLog("编辑角色(id={$id})");

        return response()->json($this->success('编辑成功'));
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
     * 分配权限初始化
     */
    public function initPermission(int $id)
    {
        $where = [
            ['id','=',$id],
            ['parent_id','=',$this->request->adminInfo['roleId']]
        ];
        $roleInfo = DB::table('sys_roles')->where($where)->select('role_name', 'menu_ids')->first();
        if (! $roleInfo) {
            return response()->json($this->fail('非法操作'));
        }

        $field = ['id','title','parent_id'];

        if ($this->request->adminInfo['isSuper']) {
            // 过滤掉菜单管理
            $where = [
                ['id','<>',2],
                ['is_able','=',1]
            ];
            $permission = DB::table('sys_menus')->where($where)->select($field)->orderBy('sort', 'ASC')->get();
        } else {
            $permission = DB::table('sys_menus')
                ->whereIn('id', $this->request->adminInfo['menuIds'])
                ->where('is_able', 1)
                ->select($field)
                ->orderBy('sort', 'ASC')
                ->get();
        }

        return response()->json($this->success([
            'permission'=>$permission,
            'role_name'=>$roleInfo->role_name,
            'default_checked'=>$roleInfo->menu_ids
        ]));
    }

    /**
     * 提交权限分配
     * */
    public function submitPermission()
    {
        // 角色id
        $id = $this->request->input('id', 0);

        if (! $this->_isMyChildRole($id)) {
            return response()->json($this->fail('非法操作'));
        }

        $res = DB::table('sys_roles')->where('id', $id)->update(['menu_ids'=>$this->request->input('keys'), 'updated_at'=>$this->getDatetime()]);
        if (! $res) {
            return response()->json($this->fail('提交失败'));
        }

        $this->recordOperationLog("为角色(id={$id})分配权限");

        return response()->json($this->success('提交成功'));
    }

    /**
     * 预览下级
     * */
    public function viewChildren(int $id)
    {
        if ($id <= 0) {
            $id = $this->request->adminInfo['roleId'];
        }

        $data = DB::table('sys_roles')
            ->where('parent_id', $id)
            ->select('id','role_name')
            ->orderBy('id', 'DESC')
            ->get();
        if ($data->isNotEmpty()) {
            foreach ($data as $k => $v) {
                $data[$k]->leaf = ! DB::table('sys_roles')->where('parent_id', $v->id)->select('id','role_name')->exists();
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
            ['parent_id','=',$this->request->adminInfo['roleId']]
        ];
        $role_name = DB::table('sys_roles')->where($where)->value('role_name');
        if (! $role_name) {
            return response()->json($this->fail('非法操作'));
        }

        // 若存在下级，则不能删除
        if (DB::table('sys_roles')->where('parent_id', $id)->exists()) {
            return response()->json($this->fail('删除失败：该角色还有子角色'));
        }

        // 若绑定了管理员则也不能删除
        if (DB::table('sys_admins')->where('role_id', $id)->exists()) {
            return response()->json($this->fail('删除失败：该角色已绑定管理员'));
        }

        if (! DB::table('sys_roles')->where('id', $id)->delete()) {
            return response()->json($this->fail('删除失败'));
        }

        $this->recordOperationLog('删除角色：'.$role_name);
        
        return response()->json($this->success('删除成功'));
    }
}