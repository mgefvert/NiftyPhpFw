<?php

class NF_Parameter
{
    public $value;

    protected function __construct()
    {
    }

    public function __toString()
    {
        trigger_error("Illegal dereferencing of parameter in __toString() function, use value property instead.", E_USER_ERROR);
    }
}
