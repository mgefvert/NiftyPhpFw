<?php

class NF_ConfigTest extends PHPUnit_Framework_TestCase
{
    public function testConfigClassOperators()
    {
        $config = NF::config();

        $this->assertTrue($config->main instanceof NF_ConfigSectionHelper);
        $this->assertEquals('main.phtml', $config->main->template);
        $this->assertEquals('main.phtml', $config->MaiN->TemplatE);
        $this->assertNull($config->main->templateFail);
        $this->assertNull($config->mainFail->templateFail);
    }

    public function testConfigLocalTest()
    {
        $config = new NF_Config(dirname(__FILE__) . '/test.conf');

        $this->assertEquals('alpha', $config->test->mangledVar);
        $this->assertEquals('alpha', $config->test->mangledvar);
        $this->assertEquals('alpha', $config->test->MangledVar);

        $this->assertEquals('bravo', $config->test->test_underscore);
        $this->assertEquals('bravo', $config->test->test_underscore);
        $this->assertEquals('bravo', $config->test->Test_Underscore);
    }

    public function testConfigLocalTestSecond()
    {
        $config = new NF_Config(dirname(__FILE__) . '/test.conf');

        $this->assertEquals('delta', $config->test_second->test);
    }

    public function testConfigLocalTestThird()
    {
        $config = new NF_Config(dirname(__FILE__) . '/test.conf');

        $this->assertEquals('echo', $config->test_third->test);
    }

    public function testSetConfigInvalid()
    {
        $config = NF::config();
        $this->setExpectedException('NF_Exception');

        $config->main = null;
    }

    public function testSetSubConfigValid()
    {
        $config = NF::config();
        $config->main->template = null;
    }
}
