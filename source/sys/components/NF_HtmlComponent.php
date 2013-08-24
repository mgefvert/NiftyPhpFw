<?php

/**
 *  Extends NF_Component with basic HTML stuff
 *
 *  PHP Version 5.3
 *
 *  @package    NiftyFramework
 *  @author     Mats Gefvert <mats@gefvert.se>
 *  @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
abstract class NF_HtmlComponent extends NF_Component
{
    public $class;
    public $name;
    public $style;
    public $value;
    protected $html;

    protected function beforeRender()
    {
        parent::beforeRender();

        $attr = array_filter(array(
            'id'    => html($this->id),
            'class' => html($this->class),
            'name'  => html($this->name),
            'style' => html($this->style)
        ));

        $this->html = '';
        foreach($attr as $k => $v)
            $this->html .= " $k=\"$v\"";
    }
}
