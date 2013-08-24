<?php

/**
 *  Your basic Select component. Makes things easier sometimes.
 *
 *  PHP Version 5.3
 *
 *  @package    NiftyFramework
 *  @author     Mats Gefvert <mats@gefvert.se>
 *  @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_HtmlSelect extends NF_HtmlComponent
{
    public $options   = array();
    public $group     = false;
    public $allowNull = true;

    protected function renderOptions($options)
    {
        $result = '';
        foreach($options as $k => $v)
        {
            $v = html($v);
            $selected = $this->value == $k ? ' selected' : '';
            $result .= "<option value='$k'{$selected}>$v</option>";
        }

        return $result;
    }

    protected function renderItem()
    {
        $result = "<select{$this->html}>";

        if ($this->allowNull)
            $result .= '<option></option>';

        if ($this->group)
        {
            foreach($this->options as $group => $suboptions)
                $result .= '<optgroup label="' . html($group) . '">' . $this->renderOptions($suboptions) . '</optgroup>';
        }
        else
            $result .= $this->renderOptions($this->options);

        $result .= '</select>';

        return $result;
    }
}
