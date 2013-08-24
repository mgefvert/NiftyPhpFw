<?php

class NF_DateTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $dt = new NF_Date();
        $this->assertNull($dt->dateTime);

        $dt = new NF_Date(new NF_DateTime('2010-07-01 14:30:00'));
        $this->assertEquals('2010-07-01', (string)$dt);

        $dt = new NF_Date(new DateTime('2008-10-19 14:20:24'));
        $this->assertEquals('2008-10-19', (string)$dt);

        $dt = new NF_Date('2008-10-19 14:20:24');
        $this->assertEquals('2008-10-19', (string)$dt);

        $dt2 = new NF_Date($dt);
        $this->assertSame((string)$dt, (string)$dt2);
    }

    public function testCompare()
    {
        $t1 = new NF_Date('now');
        $t2 = new NF_Date('tomorrow');

        $this->assertSame(0, NF_Date::compare($t1, $t1));
        $this->assertSame(0, NF_Date::compare($t2, $t2));
        $this->assertTrue(NF_Date::compare($t1, $t2) < 0);
        $this->assertTrue(NF_Date::compare($t2, $t1) > 0);

        $this->assertSame(0, NF_Date::compare('now', 'now'));
        $this->assertSame(0, NF_Date::compare('tomorrow', 'tomorrow'));
        $this->assertTrue(NF_Date::compare('now', 'tomorrow') < 0);
        $this->assertTrue(NF_Date::compare('tomorrow', 'now') > 0);
    }

    public function testInterval()
    {
        $t1 = new NF_Date('2013-05-01');
        $t2 = new NF_Date('2013-06-03');

        $interval = NF_Date::interval($t1, $t2);

        $this->assertSame(1, $interval->m);
        $this->assertSame(2, $interval->d);
        $this->assertSame(0, $interval->invert);
        $this->assertSame(33, $interval->days);
    }

    public function testDaysBetween()
    {
        $t1 = new NF_Date('2013-05-01');
        $t2 = new NF_Date('2013-06-03');

        $this->assertSame(33, NF_Date::daysBetween($t1, $t2));
        $this->assertSame(-33, NF_Date::daysBetween($t2, $t1));
    }

    public function testMax()
    {
        $dt1 = new NF_Date('now');
        $dt2 = new NF_Date('tomorrow');

        $this->assertEquals($dt2, NF_Date::max($dt1, $dt2));
    }

    public function testMin()
    {
        $dt1 = new NF_Date('now');
        $dt2 = new NF_Date('tomorrow');

        $this->assertEquals($dt1, NF_Date::min($dt1, $dt2));
    }

    public function testToday()
    {
        $dt = NF_Date::today(); $t = time();
        $this->assertSame(date('Y-m-d', $t), (string)$dt);
    }

    public function testToString()
    {
        $dt = new NF_Date();
        $this->assertSame('', (string)$dt);

        $dt = new NF_Date('2010-01-01');
        $this->assertSame('2010-01-01', (string)$dt);
    }

    public function testToValue()
    {
        $dt = new NF_Date();
        $this->assertSame(null, $dt->toValue());

        $dt = new NF_Date('2010-01-01');
        $this->assertSame('2010-01-01', $dt->toValue());
    }

    public function testAdd()
    {
        $dt = new NF_Date('2010-01-01');
        $dt->add(new DateInterval('P1Y2D'));
        $this->assertSame('2011-01-03', (string)$dt);
    }

    public function testClear()
    {
        $dt = NF_Date::today();
        $dt->clear();
        $this->assertTrue($dt->isNull());
        $this->assertNull($dt->dateTime);
    }

    public function testFormat()
    {
        $dt = new NF_Date('2008-03-05');

        $this->assertEquals(
            $dt->format('d-j-m-n-Y-y-\s-1234567890'),
            '05-5-03-3-2008-08-s-1234567890'
        );
    }

    public function testGetDate()
    {
        $dt = new NF_Date('2008-10-19');
        $x = $dt->getDate();

        $this->assertSame(2008, $x['year']);
        $this->assertSame(10, $x['month']);
        $this->assertSame(19, $x['day']);
        $this->assertSame(0, $x['wday']);

        $this->assertSame(2008, $dt->getYear());
        $this->assertSame(10, $dt->getMonth());
        $this->assertSame(19, $dt->getDay());

        $dt = new NF_Date();
        $this->assertSame(null, $dt->getDate());
        $this->assertSame(null, $dt->getYear());
        $this->assertSame(null, $dt->getMonth());
        $this->assertSame(null, $dt->getDay());
    }

    public function testGetIsoWeekday()
    {
        $dt = new NF_Date('2013-05-11');
        $this->assertEquals(6, $dt->getIsoWeekday());  // Saturday

        $dt->add('P1D');
        $this->assertEquals(7, $dt->getIsoWeekday());  // Sunday

        $dt->add('P1D');
        $this->assertEquals(1, $dt->getIsoWeekday());  // Monday

        $dt->add('P1D');
        $this->assertEquals(2, $dt->getIsoWeekday());  // Tuesday
    }

    public function testGetWeek()
    {
        $dt = new NF_Date('2012-01-01');
        $this->assertSame(52, $dt->getWeek());

        $dt = new NF_Date('2013-01-01');
        $this->assertSame(1, $dt->getWeek());

        $dt = new NF_Date('2013-05-11');
        $this->assertSame(19, $dt->getWeek());
    }

    public function testGetWeekday()
    {
        $dt = new NF_Date('2013-05-11');
        $this->assertEquals(6, $dt->getWeekday());  // Saturday

        $dt->add('P1D');
        $this->assertEquals(0, $dt->getWeekday());  // Sunday

        $dt->add('P1D');
        $this->assertEquals(1, $dt->getWeekday());  // Monday

        $dt->add('P1D');
        $this->assertEquals(2, $dt->getWeekday());  // Tuesday
    }

    public function testGetWeekYear()
    {
        $dt = new NF_Date('2012-01-01');
        $dt->getWeekYear($year, $week);
        $this->assertSame(52, $week);
        $this->assertSame(2011, $year);

        $dt = new NF_Date('2013-01-01');
        $dt->getWeekYear($year, $week);
        $this->assertSame(1, $week);
        $this->assertSame(2013, $year);

        $dt = new NF_Date('2013-05-11');
        $dt->getWeekYear($year, $week);
        $this->assertSame(19, $week);
        $this->assertSame(2013, $year);
    }

    public function testIsNull()
    {
        $dt = new NF_Date();
        $this->assertTrue($dt->isNull());

        $dt = NF_Date::today();
        $this->assertFalse($dt->isNull());
    }

    public function testLeapYear()
    {
        $dt = new NF_Date('1999-01-01'); $this->assertFalse($dt->isLeapYear());
        $dt = new NF_Date('2000-01-01'); $this->assertTrue($dt->isLeapYear());
        $dt = new NF_Date('2001-01-01'); $this->assertFalse($dt->isLeapYear());
        $dt = new NF_Date('2002-01-01'); $this->assertFalse($dt->isLeapYear());
        $dt = new NF_Date('2003-01-01'); $this->assertFalse($dt->isLeapYear());
        $dt = new NF_Date('2004-01-01'); $this->assertTrue($dt->isLeapYear());
        $dt = new NF_Date('2005-01-01'); $this->assertFalse($dt->isLeapYear());
        $dt = new NF_Date('2100-01-01'); $this->assertFalse($dt->isLeapYear());
        $dt = new NF_Date('2200-01-01'); $this->assertFalse($dt->isLeapYear());
        $dt = new NF_Date('2300-01-01'); $this->assertFalse($dt->isLeapYear());
        $dt = new NF_Date('2400-01-01'); $this->assertTrue($dt->isLeapYear());
        $dt = new NF_Date('2500-01-01'); $this->assertFalse($dt->isLeapYear());
    }

    public function testSetDate()
    {
        $dt = new NF_Date();
        $dt->setDate(2008, 10, 19);
        $this->assertSame('2008-10-19', (string)$dt);
    }

    public function testSubtract()
    {
        $dt = new NF_Date('2010-05-08');
        $dt->sub(new DateInterval('P1Y2D'));
        $this->assertSame('2009-05-06', (string)$dt);
    }
}
