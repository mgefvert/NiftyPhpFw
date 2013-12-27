<?php

/**
 * NF_ConnectionInfo encapsulates connection parameters to the database.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_ConnectionInfo
{
    public $host;
    public $database;
    public $login;
    public $password;
    public $type;
    public $charset;

    public function __construct($type = null, $host = null, $database = null, $login = null, $password = null, $charset = null)
    {
        $this->type     = $type;
        $this->host     = $host;
        $this->database = $database;
        $this->login    = $login;
        $this->password = $password;
        $this->charset  = $charset;
    }

    public function getKey()
    {
        return implode('/', array($this->type, $this->host, $this->database, $this->login, $this->charset));
    }

    public function getDSN()
    {
        $charset = $this->charset ? ";charset=" . $this->charset : '';

        if (strcasecmp($this->type, 'mysqli') == 0 || strcasecmp($this->type, 'mysql') == 0)
            return "mysql:host={$this->host};dbname={$this->database}" . $charset;

        if (strcasecmp($this->type, 'sqlsrv') == 0 || strcasecmp($this->type, 'sqlserver') == 0)
            return "sqlsrv:Server={$this->host};Database={$this->database}" . $charset;

        return "{$this->type}:host={$this->host};dbname={$this->database}" . $charset;
    }
}
