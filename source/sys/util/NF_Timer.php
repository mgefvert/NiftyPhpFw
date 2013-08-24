<?php

/**
 * Measures elapsed time
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Timer
{
    public $t0;

    public function __construct()
    {
        $this->t0 = microtime(true);
    }

    public function __toString()
    {
        return sprintf("%.6f s", $this->elapsed());
    }

    public function elapsed()
    {
        return microtime(true) - $this->t0;
    }
}
