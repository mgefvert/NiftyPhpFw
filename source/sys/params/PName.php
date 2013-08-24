<?php

class PName extends NF_Parameter
{
    public function __construct($value)
    {
        $this->value =  $value !== null ? NF_Filter::name($value) : null;
    }
}
