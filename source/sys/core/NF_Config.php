<?php

/**
 *  Small helper class
 */
class NF_ConfigSectionHelper extends NF_Elements
{
    public static function manglePropName($property)
    {
        return strtolower(str_replace('-', '_', $property));
    }

    public function __construct(array $values)
    {
        foreach($values as $k => $v)
            $this->_elem[self::manglePropName($k)] = $v;
    }

    public function __get($property)
    {
        $property = self::manglePropName($property);
        return isset($this->_elem[$property]) ? $this->_elem[$property] : null;
    }

    public function __set($property, $value)
    {
        $property = self::manglePropName($property);
        $this->_elem[$property] = $value;
    }
}

/**
 *  This class loads the app/settings.php file and initializes from that. As an init
 *  file is split into sections with [section] tags, all sections are available as
 *  properties and all properties are arrays.
 *
 *  For instance, the database configuration located under the [database] section
 *  with lots of "user=myUser" and "password=secret" values underneath, are
 *  available as NF::config()->database->user and NF::config()->database->password.
 *  Any value that doesn't exist is translated into null.
 *
 *  PHP Version 5.3
 *
 *  @package  NiftyFramework
 *  @author   Mats Gefvert <mats@gefvert.se>
 *  @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Config
{
    private $_data = array();

    /**
     *  Constructor - reads the app/settings.conf file or dies.
     */
    public function __construct($filename = null)
    {
        if ($filename == null)
        {
            $appPath  = NF_Path::$app;
            $hostname = strtoupper(NF_Filter::filename(gethostname()));

            if ($this->tryLoad($appPath . "settings.$hostname.conf"))
                return;

            if ($this->tryLoad($appPath . 'settings.conf'))
                return;

        }
        else
        {
            if ($this->tryLoad($filename))
                return;
        }

        throw new NF_Exception("No configuration file found.");
    }

    protected function tryLoad($filename)
    {
        if (file_exists($filename))
        {
            $data = parse_ini_file($filename, true);
            foreach($data as $section => $values)
            {
                $section = NF_ConfigSectionHelper::manglePropName($section);
                $this->_data[$section] = new NF_ConfigSectionHelper($values);
            }

            return true;
        }
        else
            return false;
    }

    /**
     *  Dynamic getter. Returns a NF_ConfigSectionHelper object.
     *
     *  @param string $property Property
     *
     *  @return array The requested configuration section, or an empty one
     */
    public function __get($property)
    {
        $property = NF_ConfigSectionHelper::manglePropName($property);

        return isset($this->_data[$property]) ? $this->_data[$property] : new NF_ConfigSectionHelper(array());
    }

    /**
     *  Dynamic setter. All access forbidden. Throws an exception if you access it.
     *
     *  @param string $property Property name
     *  @param string $value    Value
     *
     *  @return void
     */
    public function __set($property, $value)
    {
        throw new NF_Exception('Can not modify config data');
    }

    /**
     *  Return the database connection. Looks up the appropriate values in the
     *  configuration file an returns a newly created ConnectionInfo with values.
     *
     *  @param string $dbref Optional database reference
     *
     *  @return ConnectionInfo A ConnectionInfo object
     */
    public function getDatabaseConnection($dbref = null)
    {
        if ($dbref != null && !is_string($dbref))
            throw new NF_EDatabaseError('Invalid database reference');

        $dbcfg = $this->database->elements();
        if (count($dbcfg) == 0)
            throw new NF_EDatabaseError('No database configuration assigned');

        if ($dbref == null)
            $dbref = $dbcfg['database'];
        if ($dbref <> '')
            $dbref .= '_';

        $connection           = new NF_ConnectionInfo();
        $connection->host     = $dbcfg["{$dbref}host"];
        $connection->database = $dbcfg["{$dbref}database"];
        $connection->login    = $dbcfg["{$dbref}login"];
        $connection->password = $dbcfg["{$dbref}password"];
        $connection->type     = $dbcfg["{$dbref}type"];
        $connection->charset  = isset($dbcfg["{$dbref}charset"]) ? $dbcfg["{$dbref}charset"] : null;

        return $connection;
    }
}
