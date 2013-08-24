<?php

/**
 * Common, tried methods for password encryption and checking.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Password
{
    const Cipher = 'twofish';
    const Mode = 'cfb';

    /**
     *  Generate a salt of specific length
     *
     *  @param int $len Number of bytes
     *
     *  @static
     *  @return string
     */
    private static function _salt($len)
    {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $rand     = mcrypt_create_iv($len, MCRYPT_DEV_URANDOM);

        NF_EAssertionFailed::assert(strlen($rand) == $len, 'Incorrect salt generation');

        $max = strlen($alphabet);
        $result = '';
        for($i=0; $i<$len; $i++)
            $result .= $alphabet[ord($rand[$i]) % $max];

        NF_EAssertionFailed::assert(strlen($result) == $len, 'Incorrect salt generation');

        return $result;
    }

    /**
     *  Encrypt a password, using available methods in PHP and generating
     *  salts automatically. Will use an expensive blowfish hash if available.
     *
     *  @param string $password Password to encrypt
     *
     *  @static
     *  @return string
     */
    public static function crypt($password)
    {
        if (CRYPT_BLOWFISH == 1)
            $pw = crypt($password, '$2a$09$' . self::_salt(22));
        else
            $pw = crypt($password);

        NF_EAssertionFailed::failIf(strlen($pw) < 13, 'Password generation failed');

        return $pw;
    }

    /**
     *  Compare a submitted password with an already encrypted one, and seeing if
     *  they match.
     *
     *  @param string $plain     Submitted plaintext password
     *  @param string $encrypted Encrypted password to compare against
     *
     *  @static
     *  @return bool True if match.
     */
    public static function compare($plain, $encrypted)
    {
        if (empty($plain) || empty($encrypted))
            return false;

        return strcmp(crypt($plain, $encrypted), $encrypted) == 0;
    }

    /**
     * Generate a new key
     *
     * @return string
     */
    public static function generateFormKey()
    {
        $len = mcrypt_get_key_size(self::Cipher, self::Mode);
        NF_EAssertionFailed::failIf($len < 16, 'Encryption size less than 128 bits and is unsafe');

        $key = mcrypt_create_iv($len, MCRYPT_DEV_URANDOM);
        NF_EAssertionFailed::assert(strlen($key) == $len, 'IV generation failure');

        return $key;
    }

    /**
     * Generate a new IV value
     *
     * @return string
     */
    public static function generateIV()
    {
        $len = mcrypt_get_iv_size(self::Cipher, self::Mode);

        $iv = mcrypt_create_iv($len, MCRYPT_DEV_URANDOM);
        NF_EAssertionFailed::assert(strlen($iv) == $len, 'IV generation failure');

        return $iv;
    }

    /**
     * Encrypt data using a certain key and IV
     *
     * @param string $key
     * @param string $iv
     * @param string $data
     * @return string
     */
    public static function encrypt($key, $iv, $data)
    {
        return mcrypt_encrypt(self::Cipher, $key, $data, self::Mode, $iv);
    }

    /**
     * Decrypt data using a certain key and IV
     *
     * @param string $key
     * @param string $iv
     * @param string $data
     * @return string
     */
    public static function decrypt($key, $iv, $data)
    {
        return mcrypt_decrypt(self::Cipher, $key, $data, self::Mode, $iv);
    }

    /**
     * Generate a version-4 UUID.
     *
     * @return string
     */
    public static function uuid()
    {
        $iv = mcrypt_create_iv(128, MCRYPT_DEV_RANDOM) . uniqid('', true) . microtime() . print_r($_SERVER, true);
        $r = unpack('v*', sha1($iv, true));

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            $r[1], $r[2], $r[3], $r[4] & 0x0fff | 0x4000,
            $r[5] & 0x3fff | 0x8000, $r[6], $r[7], $r[8]);
    }
}
