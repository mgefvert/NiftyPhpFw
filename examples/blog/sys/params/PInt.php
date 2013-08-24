<?php

class PInt extends NF_Parameter
{
    public function __construct($value)
    {
        $this->value = $value !== null ? (int)$value : null;
    }
}
