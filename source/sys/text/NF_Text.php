<?php

/**
 * NF_Text interfaces to either utf8_encode/decode or the iconv library
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Text
{
    static public $gotIconv = false;
    static public $charset = 'ISO-8859-1';

    public static function fromUnicode($text)
    {
        if (self::$gotIconv)
            return iconv('UTF-8', self::$charset . '//TRANSLIT', $text);
        else
            return utf8_decode($text);
    }

    public static function toUnicode($text)
    {
        if (self::$gotIconv)
            return iconv(self::$charset, 'UTF-8', $text);
        else
            return utf8_encode($text);
    }

    public static function fromUnicodeArray(&$data, $recursiveDepth = 2)
    {
        if ((is_array($data) || is_object($data)) && $recursiveDepth > 0)
        {
            foreach($data as $k => &$ref_v)
                self::fromUnicodeArray($ref_v, $recursiveDepth - 1);
        }
        else if (is_string($data))
            $data = self::fromUnicode($data);
    }

    public static function toUnicodeArray(&$data, $recursiveDepth = 2)
    {
        if ((is_array($data) || is_object($data)) && $recursiveDepth > 0)
        {
            foreach($data as $k => &$ref_v)
                self::toUnicodeArray($ref_v, $recursiveDepth - 1);
        }
        else if (is_string($data))
            $data = self::toUnicode($data);
    }

    public static function cut(&$text, $separator)
    {
        $n = strpos($text, $separator);

        if ($n === false)
        {
            $result = $text;
            $text = '';
        }
        else
        {
            $result = substr($text, 0, $n);
            $text = substr($text, $n + strlen($separator));
        }

        return $result;
    }

    public static function ellipsis($text, $length = 80)
    {
        if (strlen($text) < $length)
            return $text;

        // Search for a separator before the length break
        $result = substr($text, 0, $length);
        while($result && ctype_alnum(substr($result, -1)))
            $result = substr($result, 0, -1);

        if (!$result)
            $result = substr($text, 0, $length); // No word break found - take the whole text block

        return trim($result) . '...';
    }

    public static function minify($data)
    {
        // Trim all spaces from the beginning of lines
        $data = preg_replace('/^ +/m', '', $data);

        // Trim comments with just alphanumeric chars and spaces
        $data = preg_replace('/<!--([\s\w]+)-->/m', '', $data);

        return $data;
    }

    public static function separateTagsAndText($html)
    {
        preg_match_all('/(<[^>]*>)|([^<]+)/', $html, $matches);
        return $matches[0];
    }

    public static function suppress($text, $source)
    {
        return str_replace($text, '', $source);
    }

    public static function suppressL($text, $source)
    {
        $len = strlen($text);
        return substr($source, 0, $len) == $text
            ? substr($source, $len)
            : $source;
    }

    public static function suppressR($text, $source)
    {
        $len = strlen($text);
        return substr($source, -$len) == $text
            ? substr($source, 0, -$len)
            : $source;
    }
}

function NF_Text_init()
{
    if (($charset = NF::config()->main->charset) != '')
        NF_Text::$charset = $charset;

    NF_Text::$gotIconv = extension_loaded('iconv');
}

NF_Text_init();
