<?php

class NF_TemplateTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var NF_Template
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new NF_Template('test-template.phtml', NF_Path::$root . 'test/sys/page/TestPages/');
    }

    public function testClear()
    {
        $this->object->test = 1;
        $this->assertEquals($this->object->parse(), '<p>1</p>');

        $this->object->clear();
        $param = $this->object->elements();
        $this->assertTrue(empty($param));
        $this->assertEquals($this->object->parse(), '<p></p>');
    }
}
