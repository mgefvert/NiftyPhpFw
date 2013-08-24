<?php

/**
 * NF_ApplicationBase is a "template" object that can be inherited by the
 * application. A class by the name "Application" will automatically be
 * instantiated by Nifty, and called appropriately if certain methods exist.
 *
 * You can extend NF_ApplicationBase if you want to have easy access to those
 * callable methods - but it's not necessary.
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_ApplicationBase
{
    /**
     * Application init. Performed before any request processing.
     */
    public function init()
    {
    }

    /**
     * Pre-routing of the path - gives you a chance to examine and manipulate
     * the path if you need to. Only the "path" property has been filled in.
     *
     * @param NF_Route $route
     */
    public function preRoute(NF_Route $route)
    {
    }

    /**
     * Post-route. The correct page has now been identified, and all the fields
     * are properly filled in from the path.
     *
     * @param NF_Route $route
     */
    public function postRoute(NF_Route $route)
    {
    }

    /**
     * Before a page is invoked.
     *
     * @param NF_PageBase $page
     * @param string $cmd
     * @param array $elements
     */
    public function preInvoke(NF_PageBase $page, $cmd, $elements)
    {
    }

    /**
     * After a page is invoked.
     *
     * @param NF_PageBase $page
     * @param string $cmd
     * @param array $elements
     */
    public function postInvoke(NF_PageBase $page, $cmd, $elements)
    {

    }

    /**
     * Invoked when all the processing is done but before the result has
     * been sent back to the user.
     */
    public function finish()
    {
    }
}
