<?php

/**
 * 基类控制器
 * 
 * @since 2024.3.15
 * */

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use App\Helps\ApiResponse;

class Controller extends BaseController
{
    use ApiResponse;

    /**
     * @var $request Illuminate\Http\Request|null
     * */
    protected $request = null;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * 记录操作日志
     *
     * @since 2024.3.15
     * */
    protected function recordOperationLog(string $content): void
    {
        DB::table('sys_operation_logs')->insert([
            'admin_id'=>$this->request->adminInfo['adminId'],
            'uri'=>$this->request->path(),
            'method'=>$this->request->method(),
            'content'=>$content,
            'ipv4'=>$this->request->ip(),
            'user_agent'=>$this->request->userAgent(),
            'created_at'=>$this->getDatetime()
        ]);
    }

    /**
     * 从缓存取出私钥
     * 
     * @since 2024.3.15
     */
    protected function getPrivateKey(string $id): string
    {
        $res = Redis::get(config('rediskey.rsa').md5($id));
        return is_null($res) ? '' : json_decode($res, true)['privatekey'];
    }

    /**
     * 获取当前日期时间
     *
     * @since 2024.3.15
     * */
    protected function getDatetime(): string {
        return date('Y-m-d H:i:s');
    }
}
