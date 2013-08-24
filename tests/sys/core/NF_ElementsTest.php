<?php

class MyElem extends NF_Elements
{
    public function setCaseSensitive($val)
    {
        $this->caseSensitive = $val;
    }
}

class NF_ElementsTest extends PHPUnit_Framework_TestCase
{
    public function testElements()
    {
        $obj = new MyElem();

        $this->assertTrue($obj->test == '');
        $this->assertTrue($obj->test === null);
        $this->assertFalse($obj->test === '');

        $obj->test = 'hello';
        $this->assertEquals($obj->test, 'hello');
        $this->assertEquals($obj->TEST, '');
        $this->assertTrue($obj->exists('test'));
        $this->assertFalse($obj->exists('TEST'));

        $elem = $obj->elements();
        $this->assertEquals($elem['test'], 'hello');

        $obj->clear();
        $elem = $obj->elements();
        $this->assertEquals(count($elem), 0);

        $obj->setCaseSensitive(false);
        $obj->test = 'hello';
        $this->assertEquals($obj->Test, 'hello');
        $this->assertTrue($obj->exists('test') && $obj->exists('Test'));
    }
}
