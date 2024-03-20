<?php

/**
 * 初始化菜单和账户
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\Commons;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class InitializeController extends Controller
{
	/**
	 * 获取初始化信息
	 * */
	public function index()
	{
		// 账号昵称
		$admin = DB::table('sys_admins')->where('id', $this->request->adminInfo['adminId'])->select('account','nickname')->first();
		if (! $admin) {
			return response()->json($this->fail('用户异常'));
		}

		// 登录日志
		$loginLog = DB::table('sys_login_logs as a')
			->join('sys_admins as b', 'a.admin_id', '=', 'b.id')
			->where('a.admin_id', $this->request->adminInfo['adminId'])
			->select(DB::raw('a.*,IFNULL(b.nickname,b.account) AS adminName'))
			->orderBy('a.id', 'desc')
			->limit(5)->get();

		// 权限别名，用于按钮级权限控制
		$auth = [];
		if (! $this->request->adminInfo['isSuper']) {
			$auth = DB::table('sys_menus')
				->whereIn('menu_type', [2,3])
				->whereIn('id', $this->request->adminInfo['menuIds'])
				->where([
					['auth_alias','<>',''],
					['is_able','=',1]
				])->pluck('auth_alias');
		}
		return response()->json($this->success([
			'userInfo'=>[
				'account'=>$admin->account,
				'nickname'=>$admin->nickname
			],
			'menuInfo'=>$this->_getAdminMenu(),
			'loginLog'=>$loginLog,
			'auth'=>$auth,
			'isSuper'=>$this->request->adminInfo['isSuper']
		]));
	}

	/**
	 * 获取管理员菜单
	 * */
	private function _getAdminMenu()
	{
		$where = [
			['menu_type','<>',3],
			['is_able','=',1]
		];

		if (! $this->request->adminInfo['isSuper']) {
			return DB::table('sys_menus')
				->where($where)
				->whereIn('id', $this->request->adminInfo['menuIds'])
				->orderBy('sort')->get();
		}

		return DB::table('sys_menus')->where($where)->orderBy('sort')->get();
	}
}