<?php

class NF_PageBaseTest extends PHPUnit_Framework_TestCase
{
    public function testPascalCase()
    {
        $this->assertEquals(NF_PageBase::pascalCase('i-am-a-lollipop'), 'IAmALollipop');
        $this->assertEquals(NF_PageBase::pascalCase('i-AM-a-lolliPop'), 'IAmALollipop');
    }

    public function testInversePascalCase()
    {
        $this->assertEquals(NF_PageBase::inversePascalCase('IAmALollipop'), 'i-am-a-lollipop');
    }

    public function testPageName()
    {
        $this->assertEquals(NF_PageBase::pageName('test<a>'), 'test');
        $this->assertEquals(NF_PageBase::pageName('teståäö'), 'testaao');
        $this->assertEquals(NF_PageBase::pageName('test!"#%½&/()=?=^1'), 'test1');
        $this->assertEquals(NF_PageBase::pageName('Fun Page Name'), 'fun-page-name');
        $this->assertEquals(NF_PageBase::pageName('Funny! Funny! /&"¿½) Ook!'), 'funny-funny-ook');
    }
}
