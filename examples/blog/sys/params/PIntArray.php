<?php

class PIntArray extends NF_Parameter
{
    public function __construct($value)
    {
        if (!is_array($value))
            $value = explode(',', $value);

        $this->value = array();
        foreach($value as $v)
            if (is_numeric($v))
                $this->value[] = (int)$v;
    }
}
