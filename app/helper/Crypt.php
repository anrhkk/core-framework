<?php namespace helper;

class Crypt
{

    private $iv;

    private $securekey;

    public static function __construct()
    {
        self::$securekey = hash('sha256', config('app.key'), TRUE);
        self::$iv = mcrypt_create_iv(32);
    }

    /*
     * 加密
     * @param $input 加密字符
     * @param string $secureKey 加密key
     * @return string
     */
    public static function encrypt($input, $secureKey = '')
    {
        $secureKey = $secureKey ? hash('sha256', $secureKey, TRUE) : self::$securekey;
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $secureKey, $input, MCRYPT_MODE_ECB, self::$iv));
    }

    /*
     * 解密
     * @param $input 解密字符
     * @param string $secureKey 加密key
     * @return string
     */
    public static function decrypt($input, $secureKey = '')
    {
        $secureKey = $secureKey ? hash('sha256', $secureKey, TRUE) : self::$securekey;
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $secureKey,
            base64_decode($input), MCRYPT_MODE_ECB, self::$iv));
    }
}