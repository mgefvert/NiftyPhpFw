<?php

/**
 *  NF_IAuthentication
 *
 *  PHP Version 5.3
 *
 *  @package    NiftyFramework
 *  @author     Mats Gefvert <mats@gefvert.se>
 *  @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
interface NF_IAuthentication
{
    /**
     *  Verify the authentication. Checks to see if the user's credentials are up
     *  to code. This is where you would perhaps authenticate against a database
     *  or something.
     *
     *  @return bool true or false depending on whether the authentication succeeded
     */
    public function verifyAuthentication();
}
