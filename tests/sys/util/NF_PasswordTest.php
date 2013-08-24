<?php

class NF_PasswordTest extends PHPUnit_Framework_TestCase
{
    public function testCrypt()
    {
        $pwd = NF_Password::crypt('test');
        $this->assertTrue($pwd != 'test');
    }

    public function testCompare()
    {
        $pwd = NF_Password::crypt('test');

        $this->assertTrue(NF_Password::compare('test', $pwd));
        $this->assertFalse(NF_Password::compare('bork', $pwd));
        $this->assertFalse(NF_Password::compare('', $pwd));

        $this->assertFalse(NF_Password::compare('', ''));
        $this->assertFalse(NF_Password::compare('', NF_Password::crypt('')));
    }

    public function testGenerateFormKey()
    {
        $key = NF_Password::generateFormKey();

        $len = mcrypt_get_key_size(NF_Password::Cipher, NF_Password::Mode);
        $this->assertEquals(strlen($key), $len);
    }

    public function testGenerateIV()
    {
        $iv = NF_Password::generateIV();

        $len = mcrypt_get_iv_size(NF_Password::Cipher, NF_Password::Mode);
        $this->assertEquals(strlen($iv), $len);
    }

    public function testEncrypt()
    {
        $iv = NF_Password::generateIV();
        $data = 'In the land of the lounge lizards';
        $key = 'ABCD';

        $enc = NF_Password::encrypt($key, $iv, $data);
        $this->assertTrue($enc != $data);

        $data2 = NF_Password::decrypt($key, $iv, $enc);
        $this->assertTrue($data2 == $data);
    }

    public function testUuid()
    {
        $test = array();

        $t0 = new NF_Timer();
        $success = true;
        for($i=0; $i<10000; $i++)
        {
            $uuid = NF_Password::uuid();
            if (strlen($uuid) != 36)
                $success = false;

            $test[] = $uuid;
        }
        echo $t0;

        $this->assertTrue($success, "Failed assertion that all UUIDs are 36 characters");
        $this->assertSame(count($test), count(array_unique($test)));
    }
}
