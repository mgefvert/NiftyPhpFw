<?php

class NF_AutoLoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers NF_AutoLoader::isNiftyClass
     */
    public function testIsNiftyClass()
    {
        $this->assertTrue(NF_AutoLoader::isNiftyClass('NF'));
        $this->assertTrue(NF_AutoLoader::isNiftyClass('NF_AutoLoader'));
        $this->assertTrue(NF_AutoLoader::isNiftyClass('NF_Session'));
        $this->assertFalse(NF_AutoLoader::isNiftyClass('NF_Doesnt_Exist'));
        $this->assertFalse(NF_AutoLoader::isNiftyClass('UndefinedClass'));
        $this->assertFalse(NF_AutoLoader::isNiftyClass('PdoDatabase'));
    }
}
