<?php

/**
 * A page class that requires previous authentication. If the Session isn't
 * authenticated, EUnauthorized exceptions are thrown, which will hopefully
 * be caught by Request->invoke and redirected into 304.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_AuthPage extends NF_PageBase
{
    /**
     *  Protected, overridable method that provides default action when
     *  the page wants to throw unauthorized exceptions. Could instead redirect
     *  to specific error pages, or whatnot.
     *
     *  @return void
     */
    protected function throwForbidden($cmd = null, $elements = null)
    {
        throw new NF_EAuthenticationFailed();
    }

    /**
     *  Get an authenticationProvider object and ask it for authentication.
     *
     *  @return bool True or false.
     */
    public function verifyAuthentication()
    {
        return NF::auth()->verifyAuthentication();
    }

    /**
     *  Overrides the NF_AbstractPage::execute call to first ask if the user is
     *  authenticated. If not, throw exceptions.
     *
     *  @param string $cmd      Optional command to execute
     *  @param array  $elements Optional elements
     *
     *  @return void
     */
    public final function execute($cmd = null, $elements = null)
    {
        if ($this->verifyAuthentication())
            parent::execute($cmd, $elements);
        else
            $this->throwForbidden($cmd, $elements);
    }
}
