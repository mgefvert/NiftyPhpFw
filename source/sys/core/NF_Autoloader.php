<?php

require_once __DIR__ . '/exceptions.php';
require_once __DIR__ . '/../text/NF_Filter.php';
require_once __DIR__ . '/NF.php';
require_once __DIR__ . '/NF_Cache.php';
require_once __DIR__ . '/NF_Path.php';

/**
 * The NF_Autoloader static class handles automatic loading of classes from the file system
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_AutoLoader
{
    private static $syscache;
    private static $cache;

    public static function buildSysCache()
    {
        static::$syscache['NF_AutoLoader'] = __FILE__;
        foreach(glob(__DIR__ . '/../*/NF*.php') as $file)
            static::$syscache[str_replace('.php', '', basename($file))] = $file;
        foreach(glob(__DIR__ . '/../params/P*.php') as $file)
            static::$syscache[str_replace('.php', '', basename($file))] = $file;

        self::$cache->set('__sys_classes', static::$syscache);
    }

    public static function isNiftyClass($class)
    {
        if (!isset(static::$syscache))
            static::buildSysCache();

        return array_key_exists($class, static::$syscache);
    }

    public static function classExists($root, $className)
    {
        $fn = $root . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        return file_exists($fn);
    }

    public static function loadClass($root, $className)
    {
        $fn = $root . str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        if (($result = file_exists($fn)) == true)
            require_once $fn;
        return $result;
    }

    protected static function doAutoload($class)
    {
        if (isset(static::$syscache[$class]))
        {
            require_once static::$syscache[$class];
            return true;
        }

        if (class_exists('NF_Path', false))
            return self::loadClass(NF_Path::$classes, $class) || self::loadClass(NF_Path::$components, $class);

        return false;
    }

    public static function autoload($class)
    {
        // We have to instantiate NF_Cache directly, because we can't risk
        // using NF::cache() when the autoloader isn't initialized yet.
        if (self::$cache == null)
            self::$cache = new NF_Cache();

        if (self::$cache->exists('__sys_classes'))
            static::$syscache = self::$cache->get('__sys_classes');

        $result = self::doAutoload($class);

        if (!$result)
        {
            // Hmm? Didn't work? Let's rebuild the system cache and try one more time.
            static::buildSysCache();
            $result = self::doAutoload($class);
        }

        return $result;
    }
}

if (!spl_autoload_register(array('NF_Autoloader', 'autoload')))
    die('Nifty fatal error: Unable to register autoloader');
