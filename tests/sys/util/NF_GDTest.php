<?php

class NF_GDTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var NF_GD
     */
    protected $object;

    protected function setUp()
    {
        $this->object = new NF_GD();
    }

    protected function tearDown()
    {
        if (file_exists(__DIR__ . '/__test.jpg')) unlink(__DIR__ . '/__test.jpg');
        if (file_exists(__DIR__ . '/__test.png')) unlink(__DIR__ . '/__test.png');
    }

    public function test__construct()
    {
        $gd = new NF_GD();
        $this->assertFalse(is_resource($gd->data));
        $this->assertTrue($gd->data === null);

        $gd->create(320, 240);
        $this->assertTrue(is_resource($gd->data));

        $data = imagecreatetruecolor(320, 240);
        $gd2 = new NF_GD($data);
        $this->assertTrue(is_resource($gd2->data));
        $this->assertEquals(240, imagesy($gd2->data));

        $jpg = $gd->returnJPEG();
        $this->assertEquals('JFIF', substr($jpg, 6, 4));

        $gd2 = new NF_GD($jpg);
        $this->assertTrue(is_resource($gd2->data));
    }

    public function testCreate()
    {
        $this->assertTrue($this->object->create(320, 240));
        $this->assertTrue(is_resource($this->object->data));
        $this->assertEquals(320, imagesx($this->object->data));
        $this->assertEquals(240, imagesy($this->object->data));
    }

    public function testLoad()
    {
        $this->assertTrue($this->object->load(file_get_contents(__DIR__ . '/test.jpg')));
        $this->assertTrue(is_resource($this->object->data));
        $this->assertEquals(37, imagesx($this->object->data));
        $this->assertEquals(32, imagesy($this->object->data));
    }

    public function testLoadJPEG()
    {
        $this->assertTrue($this->object->loadJPEG(__DIR__ . '/test.jpg'));
        $this->assertTrue(is_resource($this->object->data));
        $this->assertEquals(37, imagesx($this->object->data));
        $this->assertEquals(32, imagesy($this->object->data));
    }

    public function testLoadPNG()
    {
        $this->assertTrue($this->object->loadPNG(__DIR__ . '/test.png'));
        $this->assertTrue(is_resource($this->object->data));
        $this->assertEquals(37, imagesx($this->object->data));
        $this->assertEquals(32, imagesy($this->object->data));
    }

    public function testSaveJPEG()
    {
        $this->object->loadPNG(__DIR__ . '/test.png');
        $this->assertTrue($this->object->saveJPEG(__DIR__ . '/__test.jpg'));
        $this->assertTrue(file_exists(__DIR__ . '/__test.jpg'));

        $data = file_get_contents(__DIR__ . '/__test.jpg');
        $this->assertEquals('JFIF', substr($data, 6, 4));
    }

    public function testSavePNG()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        $this->assertTrue($this->object->savePNG(__DIR__ . '/__test.png'));
        $this->assertTrue(file_exists(__DIR__ . '/__test.png'));

        $data = file_get_contents(__DIR__ . '/__test.png');
        $this->assertEquals('PNG', substr($data, 1, 3));
    }

    public function testOutputJPEG()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        ob_start();
        $this->object->outputJPEG();
        $data = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('JFIF', substr($data, 6, 4));
    }

    public function testOutputPNG()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        ob_start();
        $this->object->outputPNG();
        $data = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('PNG', substr($data, 1, 3));
    }

    public function testReturnJPEG()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        $data = $this->object->returnJPEG();

        $this->assertEquals('JFIF', substr($data, 6, 4));
    }

    public function testReturnPNG()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        $data = $this->object->returnPNG();

        $this->assertEquals('PNG', substr($data, 1, 3));
    }

    public function testHeight()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        $this->assertEquals(32, $this->object->height());
        $this->object->loadPNG(__DIR__ . '/test.png');
        $this->assertEquals(32, $this->object->height());
    }

    public function testWidth()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        $this->assertEquals(37, $this->object->width());
        $this->object->loadPNG(__DIR__ . '/test.png');
        $this->assertEquals(37, $this->object->width());
    }

    public function testResample()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        $this->assertTrue($this->object->resample(40, 50));
        $this->assertEquals(40, $this->object->width());
        $this->assertEquals(50, $this->object->height());
    }

    public function testScale()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        $this->assertTrue($this->object->scale(370, 370));
        $this->assertEquals(370, $this->object->width());
        $this->assertEquals(320, $this->object->height());
    }

    public function testCopy()
    {
        $this->object->loadJPEG(__DIR__ . '/test.jpg');
        $gd2 = $this->object->copy();

        $this->assertEquals('NF_GD', get_class($gd2));
        $this->assertFalse($this->object === $gd2);
        $this->assertFalse($this->object->data === $gd2->data);

        $this->assertTrue($this->object->width() === $gd2->width());
        $this->assertTrue($this->object->height() === $gd2->height());
    }
}
