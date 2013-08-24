<?php

class NF_DateTimeTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        NF_TimeZone::setLocal('Europe/Stockholm');
    }

    public function testConstruct()
    {
        $dt = new NF_DateTime();
        $this->assertNull($dt->dateTime);

        $dt = new NF_DateTime(new NF_Date('2010-07-01'));
        $this->assertEquals('2010-07-01 00:00:00', (string)$dt);

        $dt = new NF_DateTime(new DateTime('2008-10-19 14:20:24'));
        $this->assertEquals('2008-10-19 14:20:24', (string)$dt);

        $dt = new NF_DateTime('2008-10-19 14:20:24');
        $this->assertEquals('2008-10-19 14:20:24', (string)$dt);

        $dt2 = new NF_DateTime($dt);
        $this->assertSame((string)$dt, (string)$dt2);

        $dt = new NF_DateTime('2010-01-01 00:00:00', NF_TimeZone::utc());
        $this->assertEquals('2010-01-01T00:00:00+00:00', $dt->format('c'));
        $dt = new NF_DateTime('2010-07-01 00:00:00', NF_TimeZone::utc());
        $this->assertEquals('2010-07-01T00:00:00+00:00', $dt->format('c'));

        $dt = new NF_DateTime('2010-01-01 00:00:00', NF_TimeZone::local());
        $this->assertEquals('2010-01-01T00:00:00+01:00', $dt->format('c'));
        $dt = new NF_DateTime('2010-07-01 00:00:00', NF_TimeZone::local());
        $this->assertEquals('2010-07-01T00:00:00+02:00', $dt->format('c'));
    }

    public function testCompare()
    {
        $t1 = new NF_DateTime('now');
        $t2 = new NF_DateTime('tomorrow');

        $this->assertSame(0, NF_DateTime::compare($t1, $t1));
        $this->assertSame(0, NF_DateTime::compare($t2, $t2));
        $this->assertTrue(NF_DateTime::compare($t1, $t2) < 0);
        $this->assertTrue(NF_DateTime::compare($t2, $t1) > 0);

        $this->assertSame(0, NF_DateTime::compare('now', 'now'));
        $this->assertSame(0, NF_DateTime::compare('tomorrow', 'tomorrow'));
        $this->assertTrue(NF_DateTime::compare('now', 'tomorrow') < 0);
        $this->assertTrue(NF_DateTime::compare('tomorrow', 'now') > 0);
    }

    public function testInterval()
    {
        $t1 = new NF_DateTime('2013-05-01 13:30:00');
        $t2 = new NF_DateTime('2013-05-01 14:40:00');

        $interval = NF_DateTime::interval($t1, $t2);

        $this->assertSame(1, $interval->h);
        $this->assertSame(10, $interval->i);
        $this->assertSame(0, $interval->invert);
    }

    public function testDaysBetween()
    {
        $t1 = new NF_DateTime('2013-05-01 00:00:00');
        $t2 = new NF_DateTime('2013-05-02 12:00:00');

        $this->assertSame(1.5, NF_DateTime::daysBetween($t1, $t2));
        $this->assertSame(-1.5, NF_DateTime::daysBetween($t2, $t1));
    }

    public function testFromDouble()
    {
        $dt = NF_DateTime::fromDouble(39740.5975);
        $this->assertSame('2008-10-19T14:20:24+02:00', (string)$dt->format('c'));
    }

    public function testFromTimestamp()
    {
        $dt = NF_DateTime::fromTimestamp(1368284883);
        $this->assertSame('2013-05-11T17:08:03+02:00', $dt->format('c'));
    }

    public function testMax()
    {
        $dt1 = new NF_DateTime('now');
        $dt2 = new NF_DateTime('tomorrow');

        $this->assertEquals($dt2, NF_DateTime::max($dt1, $dt2));
    }

    public function testMin()
    {
        $dt1 = new NF_DateTime('now');
        $dt2 = new NF_DateTime('tomorrow');

        $this->assertEquals($dt1, NF_DateTime::min($dt1, $dt2));
    }

    public function testNow()
    {
        $dt = NF_DateTime::now(); $t = time();
        $this->assertSame($t, $dt->getTimestamp());

        $dt = NF_DateTime::now(NF_TimeZone::local()); $t = time();
        $this->assertSame($t, $dt->getTimestamp());

        $dt = NF_DateTime::now(NF_TimeZone::utc()); $t = time();
        $this->assertSame($t, $dt->getTimestamp());

        $dt = NF_DateTime::now(NF_TimeZone::local()); $t = time();
        $this->assertSame(date('c', $t), $dt->format('c'));

        $dt = NF_DateTime::now(NF_TimeZone::utc()); $t = time();
        $this->assertNotSame(date('c', $t), $dt->format('c'));
    }

    public function testToday()
    {
        $dt = NF_DateTime::today(); $t = time();
        $this->assertSame(date('Y-m-d', $t) . ' 00:00:00', (string)$dt);
    }

    public function testToString()
    {
        $dt = new NF_DateTime();
        $this->assertSame('', (string)$dt);

        $dt = new NF_DateTime('2010-01-01 04:00:00');
        $this->assertSame('2010-01-01 04:00:00', (string)$dt);
    }

    public function testToValue()
    {
        $dt = new NF_DateTime();
        $this->assertSame(null, $dt->toValue());

        $dt = new NF_DateTime('2010-01-01 04:00:00');
        $this->assertSame('2010-01-01 04:00:00', $dt->toValue());
    }

    public function testAdd()
    {
        $dt = new NF_DateTime('2010-01-01 00:00');
        $dt->add(new DateInterval('P1Y2D'));
        $this->assertSame('2011-01-03 00:00:00', (string)$dt);

        $dt = new NF_DateTime('2010-01-01 00:00');
        $dt->add('P1MT2H4S');
        $this->assertSame('2010-02-01 02:00:04', (string)$dt);
    }

    public function testClear()
    {
        $dt = NF_DateTime::now();
        $dt->clear();
        $this->assertTrue($dt->isNull());
        $this->assertNull($dt->dateTime);
    }

    public function testFormat()
    {
        $dt = new NF_DateTime('2008-03-05 01:02:03');

        $this->assertEquals(
            $dt->format('d-j-m-n-Y-y-H-G-i-s-\s-1234567890'),
            '05-5-03-3-2008-08-01-1-02-03-s-1234567890'
        );
    }

    public function testGetDateTime()
    {
        $dt = new NF_DateTime('2008-10-19 14:20:24');
        $x = $dt->getDateTime();

        $this->assertSame(2008, $x['year']);
        $this->assertSame(10, $x['month']);
        $this->assertSame(19, $x['day']);
        $this->assertSame(14, $x['hour']);
        $this->assertSame(20, $x['minute']);
        $this->assertSame(24, $x['second']);
        $this->assertSame(0, $x['wday']);

        $this->assertSame(2008, $dt->getYear());
        $this->assertSame(10, $dt->getMonth());
        $this->assertSame(19, $dt->getDay());
        $this->assertSame(14, $dt->getHour());
        $this->assertSame(20, $dt->getMinute());
        $this->assertSame(24, $dt->getSecond());

        $dt = new NF_DateTime();
        $this->assertSame(null, $dt->getDateTime());
        $this->assertSame(null, $dt->getYear());
        $this->assertSame(null, $dt->getMonth());
        $this->assertSame(null, $dt->getDay());
        $this->assertSame(null, $dt->getHour());
        $this->assertSame(null, $dt->getMinute());
        $this->assertSame(null, $dt->getSecond());
    }

    public function testGetDouble()
    {
        $dt = new NF_DateTime('2008-10-19 14:20:24');
        $this->assertSame(39740.5975 , $dt->getDouble());
    }

    public function testGetIsoWeekday()
    {
        $dt = new NF_DateTime('2013-05-11');
        $this->assertEquals(6, $dt->getIsoWeekday());  // Saturday

        $dt->add('P1D');
        $this->assertEquals(7, $dt->getIsoWeekday());  // Sunday

        $dt->add('P1D');
        $this->assertEquals(1, $dt->getIsoWeekday());  // Monday

        $dt->add('P1D');
        $this->assertEquals(2, $dt->getIsoWeekday());  // Tuesday
    }

    public function testOffset()
    {
        $dt = new NF_DateTime('2013-05-11', NF_TimeZone::local());
        $this->assertEquals(2 * 3600, $dt->getOffset());

        $dt = new NF_DateTime('2013-05-11', NF_TimeZone::utc());
        $this->assertEquals(0, $dt->getOffset());

        $dt = new NF_DateTime('2013-05-11', new DateTimeZone('America/New_York'));
        $this->assertEquals(-4 * 3600, $dt->getOffset());
    }

    public function testGetTimestamp()
    {
        $dt = NF_DateTime::now(NF_TimeZone::local()); $t = time();
        $this->assertEquals($t, $dt->getTimestamp());

        $dt = NF_DateTime::now(NF_TimeZone::utc()); $t = time();
        $this->assertEquals($t, $dt->getTimestamp());
    }

    public function testGetTimezone()
    {
        $dt = new NF_DateTime();
        $this->assertSame(null, $dt->getTimezone());

        $dt = NF_DateTime::now(NF_TimeZone::local());
        $this->assertEquals(new DateTimeZone(date_default_timezone_get()), $dt->getTimezone());

        $dt = NF_DateTime::now(NF_TimeZone::utc());
        $this->assertEquals(new DateTimeZone('UTC'), $dt->getTimezone());
    }

    public function testGetTimezoneName()
    {
        $dt = new NF_DateTime();
        $this->assertSame(null, $dt->getTimezoneName());

        $dt = NF_DateTime::now(NF_TimeZone::local());
        $this->assertSame('Europe/Stockholm', $dt->getTimezoneName());

        $dt = NF_DateTime::now(NF_TimeZone::utc());
        $this->assertSame('UTC', $dt->getTimezoneName());
    }

    public function testGetWeek()
    {
        $dt = new NF_DateTime('2012-01-01');
        $this->assertSame(52, $dt->getWeek());

        $dt = new NF_DateTime('2013-01-01');
        $this->assertSame(1, $dt->getWeek());

        $dt = new NF_DateTime('2013-05-11');
        $this->assertSame(19, $dt->getWeek());
    }

    public function testGetWeekday()
    {
        $dt = new NF_DateTime('2013-05-11');
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
        $dt = new NF_DateTime('2012-01-01');
        $dt->getWeekYear($year, $week);
        $this->assertSame(52, $week);
        $this->assertSame(2011, $year);

        $dt = new NF_DateTime('2013-01-01');
        $dt->getWeekYear($year, $week);
        $this->assertSame(1, $week);
        $this->assertSame(2013, $year);

        $dt = new NF_DateTime('2013-05-11');
        $dt->getWeekYear($year, $week);
        $this->assertSame(19, $week);
        $this->assertSame(2013, $year);
    }

    public function testIsNull()
    {
        $dt = new NF_DateTime();
        $this->assertTrue($dt->isNull());

        $dt = NF_DateTime::now();
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
        $dt = new NF_DateTime();
        $dt->setDate(2008, 10, 19);
        $this->assertSame('2008-10-19 00:00:00', (string)$dt);

        $dt = new NF_DateTime('2013-01-01 12:30:05');
        $dt->setDate(2008, 10, 19);
        $this->assertSame('2008-10-19 12:30:05', (string)$dt);
    }

    public function testSetDateTime()
    {
        $dt = new NF_DateTime();
        $dt->setDateTime(2008, 10, 19, 12, 30, 5);
        $this->assertSame('2008-10-19 12:30:05', (string)$dt);

        $dt = NF_DateTime::now();
        $dt->setDateTime(2008, 10, 19, 12, 30, 5);
        $this->assertSame('2008-10-19 12:30:05', (string)$dt);
    }

    public function testSetTime()
    {
        $dt = new NF_DateTime();
        $dt->setTime(12, 30, 5);
        $this->assertSame(date('Y-m-d') . ' 12:30:05', (string)$dt);

        $dt = new NF_DateTime('2013-01-01 01:02:03');
        $dt->setTime(12, 30, 5);
        $this->assertSame('2013-01-01 12:30:05', (string)$dt);
    }

    public function testSetTimestamp()
    {
        $dt = NF_DateTime::now();
        $dt->setTimestamp(1368284883);
        $this->assertSame('2013-05-11T17:08:03+02:00', $dt->format('c'));
    }

    public function testSetTimezone()
    {
        $dt = new NF_DateTime('2010-01-01 04:00:00');
        $this->assertSame('2010-01-01 04:00:00', (string)$dt);
        $dt->setTimezone(NF_Timezone::utc());
        $this->assertSame('2010-01-01 03:00:00', (string)$dt);
    }

    public function testSubtract()
    {
        $dt = new NF_DateTime('2010-05-08 14:30');
        $dt->sub(new DateInterval('P1Y2D'));
        $this->assertSame('2009-05-06 14:30:00', (string)$dt);

        $dt = new NF_DateTime('2010-05-08 14:30');
        $dt->sub('P1MT2H4S');
        $this->assertSame('2010-04-08 12:29:56', (string)$dt);
    }

    public function testToLocal()
    {
        $dt = new NF_DateTime('2010-01-01 14:30', NF_TimeZone::local());
        $dt2 = $dt->toLocal();
        $this->assertSame($dt, $dt2);
        $this->assertSame($dt->dateTime, $dt2->dateTime);

        $dt = new NF_DateTime('2010-01-01 14:30', NF_TimeZone::utc());
        $dt2 = $dt->toLocal();
        $this->assertNotSame($dt, $dt2);
        $this->assertNotSame($dt->dateTime, $dt2->dateTime);
        $this->assertSame('2010-01-01 15:30:00', (string)$dt2);
    }

    public function testToUtc()
    {
        $dt = new NF_DateTime('2010-01-01 14:30', NF_TimeZone::utc());
        $dt2 = $dt->toUtc();
        $this->assertSame($dt, $dt2);
        $this->assertSame($dt->dateTime, $dt2->dateTime);

        $dt = new NF_DateTime('2010-01-01 14:30', NF_TimeZone::local());
        $dt2 = $dt->toUtc();
        $this->assertNotSame($dt, $dt2);
        $this->assertNotSame($dt->dateTime, $dt2->dateTime);
        $this->assertSame('2010-01-01 13:30:00', (string)$dt2);
    }
}
