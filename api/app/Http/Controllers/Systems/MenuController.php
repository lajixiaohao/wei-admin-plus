<?php

/**
 * 菜单管理
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\Systems;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * @var $allowMethods array 接口支持的RESTful请求方式
     * */
    private $allowMethods = ['GET', 'POST', 'PATCH', 'DELETE'];

    /**
     * 菜单列表
     * */
    public function index()
    {
        $parent_id = $this->request->input('parent_id', 0);

        $data = DB::table('sys_menus')->where('parent_id', $parent_id)->orderBy('sort')->get()->toArray();
        if ($data) {
            foreach ($data as $k => $v) {
                $data[$k]->hasChildren = DB::table('sys_menus')->where('parent_id', $v->id)->exists();
            }
        }

        return response()->json($this->success($data));
    }

    /**
     * 可选择的父级菜单
     * */
    public function optionalMenu()
    {
        return response()->json($this->success([
            'list'=>DB::table('sys_menus')->whereIn('menu_type', [1,2])->select('id', 'title', 'parent_id')->orderBy('sort')->get()
        ]));
    } 

    /**
     * 添加菜单或接口
     * */
    public function add()
    {
        $menu_type = $this->request->input('menu_type');
        if (! in_array($menu_type, [1,2,3,4])) {
            return response()->json($this->fail('菜单类型有误'));
        }

        $title = $this->request->input('title');
        if (! $title) {
            return response()->json($this->fail('请输入'.$this->_getMenuType($menu_type).'名称'));
        }

        $parent_id = $this->request->input('parent_id', 0);

        if ($parent_id == 0 && in_array($menu_type, [2,3])) {
            return response()->json($this->fail($this->_getMenuType($menu_type).'不能作为顶级菜单'));
        }

        $method = $this->request->input('method');
        if ($menu_type == 3 && ! in_array($method, $this->allowMethods)) {
            return response()->json($this->fail('不支持的请求方式'));
        }

        // 同一父级菜单下，名称不能相同
        $where = [
            ['parent_id','=',$parent_id],
            ['title','=',$title]
        ];
        if (DB::table('sys_menus')->where($where)->exists()) {
            return response()->json($this->fail('同一父级菜单下，该名称已存在'));
        }

        $id = DB::table('sys_menus')->insertGetId([
            'parent_id'=>$parent_id,
            'title'=>$title,
            'menu_type'=>$menu_type,
            'page_path'=>$this->request->input('page_path'),
            'dirname'=>$this->request->input('dirname'),
            'file_route_name'=>$this->request->input('file_route_name'),
            'uri'=>$this->request->input('uri'),
            'auth_alias'=>$this->request->input('auth_alias'),
            'method'=>$menu_type == 3 ? $method : '',
            'icon'=>$this->request->input('icon'),
            'sort'=>$this->request->input('sort', 1),
            'is_cache'=>$this->request->input('is_cache'),
            'is_able'=>$this->request->input('is_able'),
        ]);
        if ($id <= 0) {
            return response()->json($this->fail('添加失败'));
        }

        return response()->json($this->success('添加成功'));
    }

    /**
     * 返回菜单类型名称
     * */
    private function _getMenuType(int $type): string
    {
        switch ($type) {
            case 1:
                $name = '普通菜单';
                break;
            case 2:
                $name = '动态菜单';
                break;
            case 3:
                $name = '接口';
                break;
            case 4:
                $name = '外链';
                break;
            
            default:
                $name = '未知';
                break;
        }

        return $name;
    }

    /**
     * 编辑菜单或接口
     * */
    public function edit()
    {
        $id = $this->request->input('id');

        if (! DB::table('sys_menus')->where('id', $id)->exists()) {
            return response()->json($this->fail('非法操作'));
        }

        $menu_type = $this->request->input('menu_type');
        if (! in_array($menu_type, [1,2,3,4])) {
            return response()->json($this->fail('菜单类型有误'));
        }

        $title = $this->request->input('title');
        if (! $title) {
            return response()->json($this->fail('请输入'.$this->_getMenuType($menu_type).'名称！'));
        }

        $method = $this->request->input('method');
        if ($menu_type == 3 && ! in_array($method, $this->allowMethods)) {
            return response()->json($this->fail('不支持的请求方式'));
        }

        // 同一父级菜单下，名称不能相同
        $parent_id = $this->request->input('parent_id', 0);
        $where = [
            ['parent_id','=',$parent_id],
            ['id','<>',$id],
            ['title','=',$title]
        ];
        if (DB::table('sys_menus')->where($where)->exists()) {
            return response()->json($this->fail('同一父级菜单下，该名称已存在'));
        }

        $res = DB::table('sys_menus')->where('id', $id)->update([
            'title'=>$title,
            'page_path'=>$this->request->input('page_path'),
            'dirname'=>$this->request->input('dirname'),
            'file_route_name'=>$this->request->input('file_route_name'),
            'uri'=>$this->request->input('uri'),
            'auth_alias'=>$this->request->input('auth_alias'),
            'method'=>$menu_type == 3 ? $method : '',
            'icon'=>$this->request->input('icon'),
            'sort'=>$this->request->input('sort', 1),
            'is_cache'=>$this->request->input('is_cache'),
            'is_able'=>$this->request->input('is_able'),
        ]);
        if ($res === false) {
            return response()->json($this->fail('编辑失败'));
        }

        return response()->json($this->success('编辑成功'));
    }

    /**
     * 删除
     * */
    public function delete(int $id)
    {
        $title = DB::table('sys_menus')->where('id', $id)->value('title');
        if (! $title) {
            return response()->json($this->fail('非法操作'));
        }

        // 所有下级ID
        $ids = $this->_getChildrenMenuId($id);
        $ids[] = $id;

        DB::table('sys_menus')->whereIn('id', $ids)->delete();
        
        return response()->json($this->success('删除成功'));
    }

    /**
     * 获取所有下级菜单或接口的ID
     * */
    private function _getChildrenMenuId(int $parentId = 0, array &$ids = []): array
    {
        $data = DB::table('sys_menus')->where('parent_id', $parentId)->select('id')->get();
        if ($data->isNotEmpty()) {
            foreach ($data as $v) {
                $ids[] = $v->id;
                $this->_getChildrenMenuId($v->id, $ids);
            }
        }

        return $ids;
    }
}