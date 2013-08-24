<?php

/**
 * Page class. Derive your pages from this for default page behavior.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Page extends NF_PageBase
{
    /**
     *  Execute a call. In this case, call NF_PageBase.
     *
     *  @param string $cmd      Optional command to execute
     *  @param array  $elements Optional elements
     */
    public final function execute($cmd = null, $elements = null)
    {
        parent::execute($cmd, $elements);
    }
}
