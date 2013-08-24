<?php

/**
 * The session class provides access to session variables and session
 * creation/removal
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Session
{
    /**
     *  Constructor for class
     */
    public function __construct()
    {
        if (session_id() == null)
            session_start();

        if ($this->key == '')
            $this->key = NF_Password::generateFormKey();
    }

    /**
     *  Dynamic getter - accesses $_SESSION variables
     *
     *  @param string $prop_name Property
     *  @return mixed Variable if found, null otherwise
     */
    public function __get($prop_name)
    {
        return isset($_SESSION[$prop_name]) ? $_SESSION[$prop_name] : null;
    }

    /**
     *  Dynamic setter - accesses $_SESSION variables
     *
     *  @param string $prop_name Property
     *  @param mixed  $value     Value
     */
    public function __set($prop_name, $value)
    {
        $_SESSION[$prop_name] = $value;
    }

    /**
     *  Access the $_SESSION array
     *
     *  @return array
     */
    public function elements()
    {
        return $_SESSION;
    }

    /**
     *  Delete a particular property in the $_SESSION array
     *
     *  @param string $key Key to delete
     */
    public function delete($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     *  Clear all variables in the $_SESSION array
     */
    public function clear()
    {
        $keys = array_keys($_SESSION);
        foreach($keys as $key)
            $this->delete($key);
    }

    /**
     *  Regenerate the session ID, in case of a sudden change of privileges (such as login).
     *  Useful to guard against session hijacking attacks.
     */
    public function regenerate()
    {
        session_regenerate_id(true);
    }

    /**
     *  End the session, destroying all cookie data and all session variables
     */
    public function end()
    {
        if (isset($_COOKIE[session_name()]))
            setcookie(session_name(), '', time()-42000, '/');

        session_destroy();
        $_SESSION = array();
    }

    public function isAuthenticated()
    {
        return NF::auth()->verifyAuthentication();
    }
}
