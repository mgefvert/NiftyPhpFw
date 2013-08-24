<?php

class NF_AuthPageTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        NF::request()->handleExceptions = false;
        NF_Path::$pages = NF_Path::$root . 'test/sys/page/TestPages/';
    }

    public function testUnauthorizedInvoke()
    {
        $this->setExpectedException('NF_EForbidden');

        NF::request()->_path = '/auth-test-page/view';
        NF::request()->invoke();
    }

    public function testAuthorizedInvoke()
    {
        $auth = NF::auth();
        $auth->authenticate(1);
        $this->assertTrue($auth->verifyAuthentication());

        NF::request()->_path = '/auth-test-page/view';
        NF::request()->invoke();
    }
}
