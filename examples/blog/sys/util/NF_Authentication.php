<?php

/**
 * Default authentication provider. Will determine if the session is authenticated
 * or not depending on a $_SESSION value called "uid". If "uid" exists and is
 * non-blank, the user is considered registered. The user can be authorized by
 * a simple "authenticate" call with the appropriate id.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Authentication implements NF_IAuthentication
{
    /**
     *  Verifies if the user is authentication by checking the $_SESSION->uid value.
     *
     *  @return bool True or false.
     */
    public function verifyAuthentication()
    {
        return (NF::session()->uid != '');
    }

    /**
     *  Authenticates the user.
     *
     *  @param mixed $uid The user identification, in any format, really.
     *  @return void
     */
    public function authenticate($uid)
    {
        NF::session()->uid = $uid;
    }
}
