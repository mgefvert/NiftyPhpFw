<?php

/**
 *  Class that encapsulates Net transfers
 *
 *  PHP Version 5.3
 *
 *  @package    NiftyFramework
 *  @author     Mats Gefvert <mats@gefvert.se>
 *  @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Net
{
    /**
     * Get a network resource through curl. Throws an exception if the transfer
     * failed.
     *
     * @param string $url
     * @return string
     */
    public static function get($url, $user = null, $password = null)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($user || $password)
            curl_setopt($ch, CURLOPT_USERPWD, "$user:$password");

        $result = curl_exec($ch);
        if ($result === false)
        {
            $err = 'curl: ' . curl_error($ch);
            curl_close($ch);
            throw new Exception($err);
        }

        curl_close($ch);
        return $result;
    }
}
