<?php

/**
 * 获取登录用户地点
 * 
 * @since 2024.3.19
 * */
namespace App\Jobs;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class LoginLocationJob extends Job
{
    public $info;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($loginId, $ip)
    {
        $this->info = ['loginId'=>$loginId, 'ip'=>$ip];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        DB::table('sys_login_logs')->where('id', $this->info['loginId'])->update(['location'=>$this->getLocation($this->info['ip'])]);
    }

    /**
     * 获取ip地址
     * */
    private function getLocation(string $ip): string
    {
        $local = '本地局域网';

        if ($ip == '127.0.0.1') {
            return '本机';
        }

        // A类
        if (preg_match('/^10\.(1\d{2}|2[0-4]\d|25[0-5]|[1-9]\d|[0-9])\.(1\d{2}|2[0-4]\d|25[0-5]|[1-9]\d|[0-9])\.(1\d{2}|2[0-4]\d|25[0-5]|[1-9]\d|[0-9])$/',$ip) === 1) {
            return $local;
        }

        // B类
        if (preg_match('/^172\.(1[6789]|2[0-9]|3[01])\.(1\d{2}|2[0-4]\d|25[0-5]|[1-9]\d|[0-9])\.(1\d{2}|2[0-4]\d|25[0-5]|[1-9]\d|[0-9])$/',$ip) === 1) {
            return $local;
        }

        // C类
        if (preg_match('/^192\.168\.(1\d{2}|2[0-4]\d|25[0-5]|[1-9]\d|[0-9])\.(1\d{2}|2[0-4]\d|25[0-5]|[1-9]\d|[0-9])$/',$ip) === 1) {
            return $local;
        }

        $unknow = '未知';

        $res = Http::timeout(3)->get('http://ip.plyz.net/ip.ashx?ip='.$ip);
        if ($res->body()) {
            $tmp = explode('|', $res->body());
            if (isset($tmp[1])) {
                return $tmp[1] == '内网IP PLYZ.NET' ? $local : $tmp[1];
            }
        }

        return $unknow;
    }
}
