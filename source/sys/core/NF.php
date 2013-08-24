<?php

/**
 * Factory object for most classes in Nifty
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF
{
    private static $config;
    private static $databases = array();
    private static $persistence = array();
    private static $objects = array();
    private static $defaults = array(
        'auth'      => 'NF_Authentication',
        'cache'     => 'NF_Cache',
        'request'   => 'NF_Request',
        'response'  => 'NF_Response',
        'route'     => 'NF_Route',
        'session'   => 'NF_Session',
        'translate' => 'NF_TranslateCSV',
    );

    /**
     * Returns the global authentication object
     * @return NF_ApplicationBase
     */
    public static function app()
    {
        if (!isset(self::$objects['app']))
            self::$objects['app'] = class_exists('Application', true)
                ? new Application()
                : new NF_ApplicationBase();

        return self::$objects['app'];
    }

    /**
     * Returns the global authentication object
     * @return NF_IAuthentication
     */
    public static function auth()
    {
        return self::get('auth');
    }

    /**
     * Returns the global cache object
     * @return NF_Cache
     */
    public static function cache()
    {
        return self::get('cache');
    }

    /**
     *  Forcibly close all active database connections.
     */
    public static function closeAllDatabases()
    {
        foreach(self::$databases as $db)
            $db->close();

        self::$databases = array();
    }

    /**
     *  Forcibly close one database connection.
     */
    public static function closeDatabase($dbref)
    {
        $key = NF::config()->getDatabaseConnection($dbref)->getKey();

        $result = array_key_exists($key, self::$databases);
        if ($result)
        {
            $db = self::$databases[$key];
            $db->close();
            unset(self::$databases[$key]);
        }

        return $result;
    }

    /**
     * Returns the global configuration object
     * @return NF_Config
     */
    public static function config()
    {
        if (!isset(self::$config))
            self::$config = new NF_Config();

        return self::$config;
    }

    /**
     *  Connect to a database. If no parameter given, connects to the default
     *  database. If so, it can be done many times in sequence - the default
     *  database is only instantiated once.
     *
     *  @param mixed $dbref An optional database reference (either string or NF_ConnectionInfo)
     *  @return NF_IDatabase
     */
    public static function db($dbref = null)
    {
        if (!($dbref instanceof NF_ConnectionInfo))
            $dbref = NF::config()->getDatabaseConnection($dbref);
        $key = $dbref->getKey();

        foreach(self::$databases as &$ref_db)
            if (!$ref_db->isOpen())
                $ref_db = null;
        self::$databases = array_filter(self::$databases);

        if (!isset(self::$databases[$key]))
            self::$databases[$key] = new NF_PdoDatabase($dbref);

        return self::$databases[$key];
    }

    /**
     *  Get an instance of a provider type. Caches instances, so only one instance
     *  is created. Throws an exception if none found.
     *
     *  @param string $name The provider tag in the configuration file
     *  @return object The requested provider
     */
    public static function get($name)
    {
        $name = NF_Filter::name($name);

        if (!isset(self::$objects[$name]))
        {
            $class = NF_Filter::name(self::config()->provider->$name);
            if (!$class && isset(self::$defaults[$name]))
                $class = NF_Filter::name(self::$defaults[$name]);

            if (!$class)
                throw new NF_Exception("No provider for $name");

            self::$objects[$name] = new $class();
        }

        return self::$objects[$name];
    }

    /**
     * Returns the request object
     * @return NF_Persistence
     */
    public static function persist($db = null)
    {
        if (!($db instanceof NF_IDatabase))
            $db = NF::db($db);
        $key = $db->connection->getKey();

        if (!isset(self::$persistence[$key]))
            self::$persistence[$key] = new NF_Persistence($db);

        return self::$persistence[$key];
    }

    /**
     * Returns the request object
     * @return NF_Request
     */
    public static function request()
    {
        return self::get('request');
    }

    /**
     * Returns the response object
     * @return NF_Response
     */
    public static function response()
    {
        return self::get('response');
    }

    /**
     * Returns the routing handler
     * @return NF_Route
     */
    public static function route()
    {
        return self::get('route');
    }

    /**
     * Returns the session handler
     * @return NF_Session
     */
    public static function session()
    {
        return self::get('session');
    }

    /**
     * Returns the translation handler
     * @return NF_TranslateCSV
     */
    public static function translate()
    {
        return self::get('translate');
    }
}
