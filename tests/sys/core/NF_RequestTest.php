<?php

class NF_RequestTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $req = NF::request();

        $req->item = '1234';
        $req->dangerous = '<script>';
        $req->handleExceptions = false;
    }

    public function testConstruct()
    {
        $req = NF::request();

        $this->assertEquals('', $req->_path);
    }

    public function testGet()
    {
        $req = NF::request();

        $this->assertEquals($req->item, 1234);
        $this->assertEquals($req->getNumeric('item'), 1234);
        $this->assertEquals($req->getNumeric('bork', 99), 99);
        $this->assertEquals($req->getSafe('item'), 1234);
        $this->assertEquals($req->getSafe('bork', 99), 99);
        $this->assertEquals($req->getSafe(''), '');;
    }

    public function testGetNumericException()
    {
        $this->setExpectedException('NF_Exception');
        NF::request()->getNumeric('dangerous');
    }

    public function testGetSafe()
    {
        $this->assertEquals('script', NF::request()->getSafe('dangerous'));
    }

    public function testPost()
    {
        $req = NF::request();

        $this->assertFalse($req->isPost());
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertTrue($req->isPost());
    }

    public function testAjax()
    {
        $req = NF::request();

        $this->assertFalse($req->isAjax());
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue($req->isAjax());
    }
}
