<?php

class NF_Net_Credentials
{
    public $username;
    public $password;

    public function __construct($username = null, $password = null)
    {
        $this->username = $username;
        $this->password = $password;
    }
}

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
    public $failOnError = true;
    public $followLocation = true;
    public $maxRedirs = 10;
    public $cookies = [];

    public function setCookie($name, $value)
    {
        $this->cookies[$name] = $value;
    }

    public function request($url, $method, $params, NF_Net_Credentials $credentials = null)
    {
        $method = strtoupper($method);

        if ($method == 'GET')
        {
            if (is_array($params))
                $url .= '?' . http_build_query($params);
            else if ($params)
                $url .= substr($params, 0, 1) != '?' ? '?' . $params : $params;
        }

        $ch = curl_init($url);
        try
        {
            curl_setopt_array($ch, array(
                CURLOPT_FAILONERROR    => $this->failOnError,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_MAXREDIRS      => $this->maxRedirs,
                CURLOPT_USERPWD        => $credentials != null ? $credentials->username . ':' . $credentials->password : null
            ));

            if (!empty($this->cookies))
            {
                $cookies = [];
                foreach($this->cookies as $k => $v)
                    $cookies[] = "$k=$v";
                curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $cookies));
            }

            switch($method)
            {
                case 'GET':
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $this->followLocation);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, $this->maxRedirs);
                    break;

                case 'POST':
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    break;

                default:
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                    break;
            }

            $result = curl_exec($ch);
            if ($result === false)
                throw new Exception($err = 'curl: ' . curl_error($ch));

            curl_close($ch);
            return $result;
        }
        catch(Exception $e)
        {
            curl_close($ch);
            throw $e;
        }
    }

    /**
     * Get a network resource through curl. Throws an exception if the transfer
     * failed.
     *
     * @param string $url
     * @return string
     */
    public function get($url, $params = null, NF_Net_Credentials $credentials = null)
    {
        return $this->request($url, 'GET', $params, $credentials);
    }

    /**
     * Get a network resource through curl. Throws an exception if the transfer
     * failed.
     *
     * @param string $url
     * @param mixed $params  Either a string or an array
     * @return string
     */
    public function post($url, $params = null, NF_Net_Credentials $credentials = null)
    {
        return $this->request($url, 'POST', $params, $credentials);
    }
}
