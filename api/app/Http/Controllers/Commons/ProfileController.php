<?php

/**
 * 个人资料管理
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\Commons;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Helps\Validate;
use App\Helps\RsaSecret;

class ProfileController extends Controller
{
    /**
     * 获取个人资料
     * */
    public function index()
    {
        $admin = DB::table('sys_admins')
            ->where('id', $this->request->adminInfo['adminId'])
            ->select('account','nickname','parent_id','created_at')->first();

        // 上级
        $superior = '';
        $superiorData = DB::table('sys_admins')->where('id', $admin->parent_id)->select('account', 'nickname')->first();
        if ($superiorData) {
            $superior = $superiorData->nickname ? $superiorData->account.'('.$superiorData->nickname.')' : $superiorData->account;
        }

        return response()->json($this->success([
            'account'=>$admin->account,
            'nickname'=>$admin->nickname,
            'superior'=>$superior,
            'created_at'=>substr($admin->created_at, 0, 10),
            'roleName'=>DB::table('sys_roles')->where('id', $this->request->adminInfo['roleId'])->value('role_name')
        ]));
    }

    /**
     * 修改资料
     * */
    public function updateProfile()
    {
        $res = DB::table('sys_admins')->where('id', $this->request->adminInfo['adminId'])->update([
            'nickname'=>$this->request->input('nickname'),
            'updated_at'=>$this->getDatetime()
        ]);
        if (! $res) {
            return response()->json($this->fail('修改失败'));
        }

        $this->recordOperationLog('修改资料');

        return response()->json($this->success('修改成功'));
    }

    /**
     * 修改密码
     * */
    public function updatePassword()
    {
        $validate = new Validate();
        $rsa = new RsaSecret();

        // 获取私钥
        $privatekey = $this->getPrivateKey($this->request->adminInfo['tokenId']);
        if (! $privatekey) {
            return response()->json($this->fail('系统异常，请稍后重试'));
        }
        
        // 原密码
        $oldPassword = $rsa->decryptPassword($this->request->input('oldPassword', ''), $privatekey);
        if (! $validate->isValidPassword($oldPassword)) {
            return response()->json($this->fail('原密码输入有误'));
        }

        // 新密码
        $newPassword = $rsa->decryptPassword($this->request->input('newPassword', ''), $privatekey);
        if (! $validate->isValidPassword($newPassword)) {
            return response()->json($this->fail('新密码输入有误'));
        }

        $encryptedOldPassword = DB::table('sys_admins')->where('id', $this->request->adminInfo['adminId'])->value('pwd');

        if (! password_verify($oldPassword, $encryptedOldPassword)) {
            return response()->json($this->fail('修改失败：原密码不正确'));
        }

        $res = DB::table('sys_admins')->where('id', $this->request->adminInfo['adminId'])->update([
            'pwd'=>password_hash($newPassword, PASSWORD_DEFAULT),
            'updated_at'=>$this->getDatetime()
        ]);
        if (! $res) {
            return response()->json($this->fail('修改失败'));
        }

        DB::table('sys_login_logs')->where('id', $this->request->adminInfo['loginId'])->update(['logout_at'=>$this->getDatetime()]);

        // 将所有在线会话全部失效
        $keys = Redis::keys("token:{$this->request->adminInfo['adminId']}*");
        if ($keys) {
            foreach ($keys as $v) {
                Redis::del($v);
            }
        }

        $this->recordOperationLog('修改密码');
        
        return response()->json($this->success('修改成功，即将退出重新登录！'));
    }
}