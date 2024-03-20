<?php

/**
 * token验证中间件
 * 
 * @since 2023.12.31
 * */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use App\Helps\ApiResponse;
use Illuminate\Support\Facades\Redis;

class TokenMiddleware
{
	use ApiResponse;

	/**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
    	try {
    		$token = '';
    		if (preg_match('/Bearer\s(\S+)/', $request->header('Authorization'), $matches) === 1) {
    			$token = $matches[1];
    		} else {
    			return response()->json($this->fail('Token Missing', 401));
    		}

            // 解密得到tokenId
    		$tokenId = Crypt::decrypt($token);
            $adminInfo = Redis::get('token:'.$tokenId);
            if (is_null($adminInfo)) {
                return response()->json($this->fail('您的登录状态已失效，请重新登录！', 401));
            }

            $adminInfo = json_decode($adminInfo, true);

            // 当前请求的设备必须与获取token时的设备一致
            if ($adminInfo['deviceId'] !== md5($request->ip().$request->userAgent())) {
                return response()->json($this->fail('您的会话环境异常，请检查后再试！', 401));
            }

            // 赋值基本信息至$request
    		$request->adminInfo = $adminInfo;
    	} catch (DecryptException $e) {
    		return response()->json($this->fail('Token invalid', 401));
    	}

        return $next($request);
    }
}