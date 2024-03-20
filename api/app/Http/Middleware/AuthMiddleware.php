<?php

/**
* 权限验证
*
* @since 2023.12.31
*/

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use App\Helps\ApiResponse;

class AuthMiddleware
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
        if (! $request->adminInfo['isSuper']) {
            $where = [
                ['menu_type','=',3],
                ['method','=',$request->method()],
                ['is_able','=',1]
            ];
            $res = DB::table('sys_menus')->where($where)->whereIn('id', $request->adminInfo['menuIds'])->pluck('uri');
            if ($res->isEmpty()) {
                return response()->json($this->fail('暂无权限'), 403);
            }
            $pass = false;
            foreach ($res as $v) {
                if (preg_match('/^'.addcslashes($v, '/').'$/', $request->path()) === 1) {
                    $pass = true;
                    break;
                }
            }
            if (! $pass) {
                return response()->json($this->fail('暂无权限'), 403);
            }
        }

        return $next($request);
    }
}