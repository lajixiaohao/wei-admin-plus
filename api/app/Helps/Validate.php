<?php

/**
 * 验证类相关助手
 * 
 * @since 2023.12.28
 * */

namespace App\Helps;

class Validate
{
	/**
    * 账号验证
    * 长度5~16
    * 
    * @since 2023.12.31
    * @param $str int|string
    */
    public function isValidAccount($str): bool {
        return preg_match('/^[a-zA-Z][a-zA-Z0-9_]{4,15}$/', $str) === 1 || $this->isValidMobile($str);
    }

    /**
     * 手机号验证
     * 
     * @since 2023.12.31
     * @param $str int|string
     */
    public function isValidMobile($str): bool {
        return preg_match('/^\d{11}$/', $str) === 1;
    }

    /**
     * 密码验证
     * 必须包含大小写字母和数字的组合，可以使用特殊字符，长度在6-20之间
     * 
     * @since 2023.12.31
     * @link https://c.runoob.com/front-end/854/
     * @param $str string
     * */
    public function isValidPassword($str): bool {
        return preg_match('/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,20}$/', $str) === 1;
    }
}