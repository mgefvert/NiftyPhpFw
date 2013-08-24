<?php

/**
 * Defines an interface for context handlers, i.e. pages, components etc.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
interface NF_IContext
{
    function ref($method = null);
    function refPath($file = null);
}
