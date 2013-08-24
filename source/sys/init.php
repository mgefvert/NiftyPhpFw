<?php

/**
 *  Initializes the whole global environment, sets up paths, global
 *  variables etc.
 *
 *  PHP Version 5.3
 *
 *  @package  NiftyFramework
 *  @author   Mats Gefvert <mats@gefvert.se>
 *  @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */

// Global configuration directives - set in index.php
global $niftyAppName;

// If no document root, fake one
if (!isset($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] == '')
    $_SERVER['DOCUMENT_ROOT'] = getcwd();

// Set up the autoloader from the start
require_once __DIR__ . '/NF_Autoloader.php';

// Check to see if we're using utf8
if (NF::config()->main->utf8)
{
    mb_internal_encoding('utf8');
    mb_language('uni');
    mb_regex_encoding('utf8');
}

// Initialize various handlers by forcing creation of their objects
NF::session();

// Utility functions

function a($obj, $key, $default = null)
{
    return NF_Arrays::access($obj, $key, $default);
}

function html($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}

function pr($var)
{
    return '<pre>' . print_r($var, true) . '</pre>';
}

function _t($str)
{
    $str = NF::translate()->translate($str);

    if (func_num_args() > 1)
    {
        $args = func_get_args();
        array_shift($args);

        $str = vsprintf($str, $args);
    }

    return $str;
}

function eq($str1, $str2)
{
    return strcmp($str1, $str2) == 0;
}

function eqcase($str1, $str2)
{
    return strcasecmp($str1, $str2) == 0;
}

function __wrap($value, ReflectionClass $class = null)
{
    if ($class == null)
        return $value;

    $parent = $class->getParentClass();
    if ($parent == null || $parent->name != 'NF_Parameter')
        return $value;

    return $class->newInstance($value);
}

function NF_invoke($object, $methodName, $params = null)
{
    $class = get_class($object);

    if (!method_exists($object, $methodName))
        throw new NF_Exception("Invalid method call: {$class}->$methodName");

    // Code kindly borrowed from php.net - call_user_func_array() example
    $reflect = new ReflectionMethod($class, $methodName);
    if ($reflect->isPublic() == false)
        throw new NF_Exception("Invalid method call: {$class}->$methodName is not public");

    $real_params = array();
    foreach ($reflect->getParameters() as $parameter)
    {
        $name = $parameter->getName();
        $class = $parameter->getClass();

        if ($class != null && $class->name == 'NF_IV')
            $real_params[] = new NF_IV();
        else if (isset($params[$name]))
            $real_params[] = __wrap($params[$name], $class);
        else if ($parameter->isDefaultValueAvailable())
            $real_params[] = __wrap($parameter->getDefaultValue(), $class);
        else
            throw new NF_Exception("Invalid method call: missing required parameter '$name'");
    }

    return call_user_func_array(array($object, $methodName), $real_params);
}
