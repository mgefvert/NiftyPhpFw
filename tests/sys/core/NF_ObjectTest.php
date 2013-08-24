<?php

class MyObject
{
    public $var = '';

    public function publicMethod($a = 'x')
    {
        $this->var = $a;
    }

    private function privateMethod()
    {
    }
}

class NF_ObjectTest extends PHPUnit_Framework_TestCase
{
    public function testObject()
    {
        $obj = new MyObject();

        NF_invoke($obj, 'publicMethod');
        $this->assertEquals($obj->var, 'x');

        NF_invoke($obj, 'publicMethod', array('a' => 'y'));
        $this->assertEquals($obj->var, 'y');
    }

    public function testInvokePrivate()
    {
        $obj = new MyObject();

        $this->setExpectedException('NF_Exception');
        NF_invoke($obj, 'privateMethod');
    }
}
