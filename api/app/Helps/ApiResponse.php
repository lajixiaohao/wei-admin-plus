<?php

/**
 * 统一返回格式封装
 * 2023.4.10
 * */

namespace App\Helps;

trait ApiResponse
{
    /**
     * 返回成功
     * */
    public function success(array|string $data = [], string $msg = 'success'): array
    {
        if (is_string($data)) {
            return ['code'=>200, 'msg'=>$data, 'data'=>[]];
        }

        return [
            'code'=>200,
            'msg'=>$msg,
            'data'=>$data
        ];
    }

    /**
     * 返回失败
     * */
    public function fail(string $msg = 'error', int $code = 201): array
    {
        return [
            'code'=>$code,
            'msg'=>$msg
        ];
    }
}