<?php

/**
 * NF_Filter
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Filter
{
    /**
     * Calls the filter_var function for the given parameters.
     *
     * @param int filter_var PHP filter function
     */
    public static function f($filter, &$var1, &$var2 = null, &$var3 = null, &$var4 = null, &$var5 = null, &$var6 = null, &$var7 = null, &$var8 = null)
    {
        ff($filter, null, $var1, $var2, $var3, $var4, $var5, $var6, $var7, $var8);
    }

    /**
     * Calls the filter_var function for the given parameters with certain
     * options.
     *
     * @param int $filter  PHP filter function
     * @param int $options Filter options
     */
    public static function ff($filter, $options, &$var1, &$var2 = null, &$var3 = null, &$var4 = null, &$var5 = null, &$var6 = null, &$var7 = null, &$var8 = null)
    {
        foreach(range(1, 8) as $k)
        {
            $var = 'var' . $k;
            if ($$var !== null)
                $$var = filter_var($$var, $filter, $options);
        }
    }

    /**
     * Transforms the parameters into either an integer or NULL.
     */
    public static function fint(&$var1, &$var2 = null, &$var3 = null, &$var4 = null, &$var5 = null, &$var6 = null, &$var7 = null, &$var8 = null)
    {
        foreach(range(1, 8) as $k)
        {
            $var = 'var' . $k;
            if ($$var !== null)
                $$var = (int)$$var;
        }
    }

    /**
     * Washes a filename by excluding the following characters:
     *   0x00-0x1F, and
     *   /:*?'"<>\|
     *
     * @param string $filename
     * @return string
     */
    public static function filename(&$filename)
    {
        return preg_replace('/[\x0-\x1F\/\:\*\?\'\"\<\>\|\\\]/', '', $filename);
    }

    /**
     * Washes a system name, makes it include only a-z, A-Z, 0-9 and underscore.
     *
     * @param string $name
     * @return string
     */
    public static function name($name)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
    }
}
