<?php

/**
 * 公钥管理
 * 
 * @since 2024.3.15
 * */

namespace App\Http\Controllers\Commons;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use App\Helps\RsaSecret;
use Ramsey\Uuid\Uuid;

class SecretKeyController extends Controller
{
    /**
     * 用于登录时加密密码
     * */
    public function loginPublicKey()
    {
        $uuid = (string) $this->request->input('uuid');
        if (! Uuid::isValid($uuid)) {
            return response()->json($this->fail('无效的uuid'));
        }

        if (is_null(Redis::get(config('rediskey.captcha').$uuid))) {
            return response()->json($this->fail('验证码无效或已过期'));
        }

        return response()->json($this->success(['publickey'=>$this->_getPublicKey($uuid)]));
    }

    /**
     * 用于登录后获取公钥加密密码
     * */
    public function loggedPublicKey() {
        return response()->json($this->success(['publickey'=>$this->_getPublicKey($this->request->adminInfo['tokenId'])]));
    }

    /**
     * 返回公钥
     * */
    private function _getPublicKey(string $id): string
    {
        $redisKey = config('rediskey.rsa').md5($id);

        $res = Redis::get($redisKey);
        if ($res) {
            return json_decode($res, true)['publickey'];
        }

        $keys = (new RsaSecret())->generateRsaKey();
        // 过期时间不宜过长
        Redis::setex($redisKey, 10, json_encode($keys));

        return $keys['publickey'];
    }
}