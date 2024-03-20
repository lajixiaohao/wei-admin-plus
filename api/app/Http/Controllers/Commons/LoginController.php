<?php

/**
 * 登录管理
 * 
 * @since 2024.3.16
 * */

namespace App\Http\Controllers\Commons;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;
use App\Helps\Validate;
use App\Helps\RsaSecret;
use App\Jobs\LoginLocationJob;

class LoginController extends Controller
{
	/**
	 * 执行登录，返回token
	 * */
	public function index()
	{
       // 验证码基本验证
        $uuid = (string) $this->request->input('uuid');
        if (! Uuid::isValid($uuid)) {
            return response()->json($this->fail('无效的uuid'));
        }
        $code = $this->request->input('code');
        if (strlen($code) !== 4) {
            return response()->json($this->fail('验证码格式有误'));
        }

        // 验证码检查
        $redisKey = config('rediskey.captcha').$uuid;
        $realCaptcha = Redis::get($redisKey);
        if (is_null($realCaptcha)) {
            return response()->json($this->fail('验证码无效或已过期'));
        }

        Redis::del($redisKey);

        if ($realCaptcha !== strtolower($code)) {
            return response()->json($this->fail('验证码不正确'));
        }

        $validate = new Validate();

        // 账号
        $account = $this->request->input('account');
        if (! $validate->isValidAccount($account)){
            return response()->json($this->fail('账号输入有误'));
        }

        // 获取私钥，以解密密码
        $privatekey = $this->getPrivateKey($uuid);
        if (! $privatekey) {
            return response()->json($this->fail('系统异常，请稍后重试'));
        }

        // 密码
        $rsaSecret = new RsaSecret();
        $pwd = $rsaSecret->decryptPassword($this->request->input('pwd', ''), $privatekey);
        if (! $validate->isValidPassword($pwd)) {
            return response()->json($this->fail('密码输入有误'));
        }

        // 账号验证
        $where = [
            ['account', '=', $account],
            ['is_able', '=', 1]
        ];
        $admin = DB::table('sys_admins')->where($where)->first();
        if (! $admin) {
            return response()->json($this->fail('账号不存在'));
        }

        if (! password_verify($pwd, $admin->pwd)) {
            return response()->json($this->fail('密码错误'));
        }

        // 必须分配角色才能登录
        $where = [
            ['id', '=', $admin->role_id],
            ['is_able', '=', 1]
        ];
        $role = DB::table('sys_roles')->where($where)->select('menu_ids')->first();
        if (! $role) {
            return response()->json($this->fail('权限异常，禁止登录'));
        }

        // 记录登录日志
        $loginId = DB::table('sys_login_logs')->insertGetId([
            'admin_id'=>$admin->id,
            'ipv4'=>$this->request->ip(),
            // 'location'=>$this->getLocation($this->request->ip()),
            'user_agent'=>$this->request->userAgent(),
            'login_at'=>$this->getDatetime()
        ]);

        // 存储token令牌信息
        $tokenId = $admin->id.time();
        Redis::setex(config('rediskey.token').$tokenId, env('TOKEN_EXPIRE', 1), json_encode([
            'tokenId'=>$tokenId,
            'adminId'=>$admin->id,
            'roleId'=>$admin->role_id,
            'isSuper'=>$admin->role_id === 1,
            'menuIds'=>$role->menu_ids ? explode(',', $role->menu_ids) : [],
            // 用于退出时，准确记录当前账号退出时间
            'loginId'=>$loginId,
            // 用于禁止跨设备使用token
            'deviceId'=>md5($this->request->ip().$this->request->userAgent())
        ]));

        dispatch(new LoginLocationJob($loginId, $this->request->ip()));

		return response()->json($this->success(['token'=>Crypt::encrypt($tokenId)], '登录成功'));
	}
}