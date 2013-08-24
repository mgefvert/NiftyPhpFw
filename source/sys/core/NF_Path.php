<?php

/**
 * The Path class shields the system from certain peculiarities. It determines
 * correct urls/paths for common functions.
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Path
{
    protected static $initialized;

    public static $root;
    public static $app;
    public static $public;
    public static $site;
    public static $siteURL;
    public static $system;
    public static $cache;
    public static $classes;
    public static $components;
    public static $pages;
    public static $templates;

    /**
     *  Initialization for NF_Path
     */
    public static function __init()
    {
        global $niftyAppName;

        if (self::$initialized) return;
        self::$initialized = true;

        $app = $niftyAppName ? '.' . NF_Filter::name($niftyAppName) : null;

        // Generate public paths
        self::$public  = self::normalize($_SERVER['DOCUMENT_ROOT'] . '/');
        self::$site    = self::normalize(dirname($_SERVER['SCRIPT_FILENAME']) . '/');
        self::$siteURL = self::normalize(dirname($_SERVER['SCRIPT_NAME']) . '/');

        // Generate sys path
        $s = self::normalize(dirname(__DIR__));

        self::$system = $s . '/';

        // Generate root path
        self::$root       = dirname($s) . "/";
        self::$app        = self::$root . "app$app/";
        self::$cache      = self::$app . "cache/";
        self::$classes    = self::$app . "classes/";
        self::$components = self::$app . "components/";
        self::$pages      = self::$app . "pages/";
        self::$templates  = self::$app . "templates/";
    }

    /**
     *  Turn all backslashes into frontslashes and correct double slashes
     *
     *  @param string $path Path to modify
     *  @return string
     */
    public static function normalize($path)
    {
        $path = str_replace('\\', '/', $path);
        $path = str_replace('//', '/', $path);

        return $path;
    }

    /**
     *  Make sure that the path always includes a trailing path delimiter
     *
     *  @param string $path
     *  @return string
     */
    public static function includeTrailingSlash($path)
    {
        if ($path[strlen($path) - 1] != '/')
            $path .= '/';

        return $path;
    }

    /**
     *  Make sure that the path excludes a trailing path delimiter
     *
     *  @param string $path
     *  @return string
     */
    public static function excludeTrailingSlash($path)
    {
        if ($path[strlen($path) - 1] == '/')
            $path = substr($path, 0, -1);

        return $path;
    }

    /**
     *  Get the extension of a file name
     *
     *  @param string $path Path to modify
     *  @return string Extension without the initial "."
     */
    public static function getExtension($path)
    {
        $info = pathinfo($path);
        return isset($info['extension']) ? $info['extension'] : null;
    }
}

NF_Path::__init();