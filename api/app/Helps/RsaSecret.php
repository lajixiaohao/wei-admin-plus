<?php

/**
 * RSA生成、加解密等助手
 * 
 * @since 2023.12.29
 * */

namespace App\Helps;

class RsaSecret
{
	/**
	 * 生成公钥私钥
	 * https://www.php.net/manual/zh/function.openssl-pkey-new.php
	 * 
	 * @since 2023.12.29
	 * */
	public function generateRsaKey(string $alg = 'sha512', int $bits = 1024)
	{
		$config = [
			'digest_alg'=>$alg,
			'private_key_bits'=>$bits,
			'private_key_type'=>OPENSSL_KEYTYPE_RSA
		];
		$res = openssl_pkey_new($config);
		$data['publickey'] = openssl_pkey_get_details($res)['key'];
		openssl_pkey_export($res, $privatekey);
		$data['privatekey'] = $privatekey;
		return $data;
	}

	/**
     * 使用公钥加密密码
     * */
    public function encryptPassword(string $str, string $publickey): string
    {
        if (openssl_public_encrypt($str, $crypted, $publickey, OPENSSL_PKCS1_PADDING)) {
            return base64_encode($crypted);
        }

        return '';
    }

    /**
     * 使用私钥解密密码
     * */
    public function decryptPassword(string $str, string $privatekey): string
    {
        if (openssl_private_decrypt(base64_decode($str), $decrypted, $privatekey, OPENSSL_PKCS1_PADDING)) {
            return $decrypted;
        }

        return '';
    }
}