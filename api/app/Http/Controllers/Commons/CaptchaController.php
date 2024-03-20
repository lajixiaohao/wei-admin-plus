<?php

/**
 * 验证码管理
 * 需要GD库扩展支持
 * 
 * @since 2024.3.15
 * */
namespace App\Http\Controllers\Commons;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;

class CaptchaController extends Controller
{
	/**
	 * 生成验证码
	 * */
	public function index()
	{
        $uuid = (string) $this->request->input('uuid');
        if (! Uuid::isValid($uuid)) {
            return response()->json($this->fail('无效的uuid'));
        }

        // 图像宽度
        $width = 120;
        // 图像高度
        $height = 46;

        // 创建图像
        $img = imagecreatetruecolor($width, $height);
        // 背景色
        $colorBg = imagecolorallocate($img, 255, 255, 255);
        // 填充颜色
        imagefill($img, 0, 0, $colorBg);

        // 设置干扰点
        for ($i = 0; $i < 100; $i++) {
            $pixColor = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($img, mt_rand(0, $width), mt_rand(0, $height), $pixColor);
        }

        // 设置干扰线
        for ($i = 0; $i < 2; $i++) {
         $lineColor = imagecolorallocate($img, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
         imageline($img, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $lineColor);
        }

        // 存放验证码
        $code = [];
        $codeSet = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefhijkmnpqrstuvwxyz';
        $codeSetLen = strlen($codeSet);
        // 4位
        $codeLen = 4;
        for ($i = 0; $i < $codeLen; $i++) {
            $code[] = $codeSet[mt_rand(0, $codeSetLen - 1)];
        }

        // 文字大小
        $fontSize = 16;

        // 字体颜色
        $fontColor = imagecolorallocate($img, mt_rand(1, 150), mt_rand(1, 150), mt_rand(1, 150));
        // 文字起始x坐标
        $x = 0;
        for ($i = 0; $i < $codeLen; $i++) {
            $x += mt_rand(intval($fontSize * 1.2), intval($fontSize * 1.6));
            imagettftext($img, $fontSize, mt_rand(-40, 40), $x, intval($fontSize * 1.6), $fontColor, storage_path('fonts/captcha.ttf'), $code[$i]);
        }

        // 获取图片流
        ob_start();
        imagepng($img);
        $imgData = ob_get_clean();
        imagedestroy($img);

        Redis::setex(config('rediskey.captcha').$uuid, env('CAPTCHA_EXPIRE', 1), strtolower(implode('', $code)));

		return response()->json($this->success(['img'=>'data:image/png;base64,'.base64_encode($imgData)]));
	}
}