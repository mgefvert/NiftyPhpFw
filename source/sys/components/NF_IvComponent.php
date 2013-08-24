<?php

/**
 *  Extends NF_Component with helpers for data transfer through IVs.
 *
 *  PHP Version 5.3
 *
 *  @package    NiftyFramework
 *  @author     Mats Gefvert <mats@gefvert.se>
 *  @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
abstract class NF_IvComponent extends NF_Component
{
    protected $helper;
    protected $persistVarList = array();

    public function init()
    {
        parent::init();
        $this->helper = new NF_IV(false);
    }

    public function postback()
    {
        parent::postback();
        $this->helper->restore();

        foreach($this->persistVarList as $v)
            $this->$v = $this->helper->$v;
    }

    protected function inject()
    {
        foreach($this->persistVarList as $v)
            $this->helper->$v = $this->$v;

        return $this->helper->inject($this->id . '_iv');
    }
}
