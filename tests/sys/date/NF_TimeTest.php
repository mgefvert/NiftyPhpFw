<?php

class NF_TimeTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $t = new NF_Time("01:02:03");
        $this->assertSame(3600 + 120 + 3, $t->asSeconds());
        $t2 = clone $t;
        $this->assertSame($t->asSeconds(), $t2->asSeconds());
        $t3 = new NF_Time($t);
        $this->assertSame($t->asSeconds(), $t3->asSeconds());

        $t = new NF_Time();
        $this->assertSame(null, $t->asSeconds());
        $t = new NF_Time('');
        $this->assertSame(null, $t->asSeconds());
        $t = new NF_Time(null);
        $this->assertSame(null, $t->asSeconds());
        $t = new NF_Time('12:00');
        $this->assertSame(43200, $t->asSeconds());
        $t = new NF_Time('48:00');
        $this->assertSame(2 * 86400, $t->asSeconds());
        $t = new NF_Time('3:20');
        $this->assertSame('03:20:00', (string)$t);
        $t = new NF_Time('3:20:43');
        $this->assertSame('03:20:43', (string)$t);
    }

    public function testFromHours()
    {
        $t = NF_Time::fromHours('3.5');
        $this->assertSame('03:30:00', (string)$t);
        $t = NF_Time::fromHours('6,5');
        $this->assertSame('06:30:00', (string)$t);
        $t = NF_Time::fromHours('40,5');
        $this->assertSame('40:30:00', (string)$t);
        $t = NF_Time::fromHours(0);
        $this->assertSame(0, $t->asSeconds());
        $t = NF_Time::fromHours(12);
        $this->assertSame(43200, $t->asSeconds());
    }

    public function testFromSeconds()
    {
        $t = NF_Time::fromSeconds(0);
        $this->assertSame(0, $t->asSeconds());

        $t = NF_Time::fromSeconds(43200);
        $this->assertSame(43200, $t->asSeconds());
    }

    public function testFromTime()
    {
        $t = NF_Time::fromTime();
        $this->assertSame(0, $t->asSeconds());
        $t = NF_Time::fromTime(12);
        $this->assertSame('12:00:00', (string)$t);
        $t = NF_Time::fromTime(12, 13);
        $this->assertEquals('12:13:00', (string)$t);
        $t = NF_Time::fromTime(12, 13, 57);
        $this->assertEquals('12:13:57', (string)$t);
    }

    public function testToString()
    {
        $t = new NF_Time();
        $this->assertSame('', (string)$t);

        $t = new NF_Time('12:03');
        $this->assertSame('12:03:00', (string)$t);
    }

    public function testToValue()
    {
        $t = new NF_Time();
        $this->assertSame(null, $t->toValue());

        $t = new NF_Time('12:03');
        $this->assertSame('12:03:00', $t->toValue());
    }

    public function testGetTime()
    {
        $t = new NF_Time();
        $this->assertSame(null, $t->getTime());

        $t = NF_Time::fromTime(13, 55, 3);
        $x = $t->getTime();

        $this->assertEquals(13, $x['hour']);
        $this->assertEquals(55, $x['minute']);
        $this->assertEquals(3, $x['second']);
    }

    public function testGetHours()
    {
        $t = new NF_Time();
        $this->assertSame(null, $t->getHours());

        for ($i=0; $i<60; $i++)
        {
            $t = new NF_Time("$i:00:00");
            $this->assertSame($i, $t->getHours());

            $t = new NF_Time("$i:59:59");
            $this->assertSame($i, $t->getHours());
        }
    }

    public function testGetMinutes()
    {
        $t = new NF_Time();
        $this->assertSame(null, $t->getMinutes());

        for ($i=0; $i<60; $i++)
        {
            $t = new NF_Time("13:$i:00");
            $this->assertSame($i, $t->getMinutes());

            $t = new NF_Time("13:$i:59");
            $this->assertSame($i, $t->getMinutes());
        }
    }

    public function testGetSecond()
    {
        $t = new NF_Time();
        $this->assertSame(null, $t->getSeconds());

        for ($i=0; $i<60; $i++)
        {
            $t = new NF_Time("13:55:$i");
            $this->assertSame($i, $t->getSeconds());
        }
    }

    public function testAsHours()
    {
        $t = new NF_Time();
        $this->assertSame(null, $t->asHours());

        $t = NF_Time::fromHours(12);
        $this->assertSame(12, $t->asHours());

        $t = NF_Time::fromHours(36);
        $this->assertSame(36, $t->asHours());
    }

    public function testAsSeconds()
    {
        $t = new NF_Time();
        $this->assertSame(null, $t->asSeconds());

        $t = NF_Time::fromHours(12);
        $this->assertSame(43200, $t->asSeconds());
    }

    public function testAdd()
    {
        $t = NF_Time::fromTime(12, 37, 57);

        $t->add();
        $this->assertSame('12:37:57', (string)$t);
        $t->add(1);
        $this->assertSame('13:37:57', (string)$t);
        $t->add(-1, 2);
        $this->assertSame('12:39:57', (string)$t);
        $t->add(0, -2, 3);
        $this->assertSame('12:38:00', (string)$t);
    }

    public function testFormat()
    {
        $t = new NF_Time('3:07:06');
        $this->assertSame('3|03|07|06|s', $t->format('G|H|i|s|\s'));
    }
}
