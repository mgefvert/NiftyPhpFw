<?php

class NF_TimeZoneTest extends PHPUnit_Framework_TestCase
{
    public function testIsLocal()
    {
        $default = NF_DateTime::now();
        $local = NF_DateTime::now(NF_TimeZone::local());
        $utc = NF_DateTime::now(NF_TimeZone::utc());

        $this->assertTrue(NF_TimeZone::isLocal($default->getTimezone()));
        $this->assertTrue(NF_TimeZone::isLocal($local->getTimezone()));
        $this->assertFalse(NF_TimeZone::isLocal($utc->getTimezone()));
    }

    public function testIsUtc()
    {
        $default = NF_DateTime::now();
        $local = NF_DateTime::now(NF_TimeZone::local());
        $utc = NF_DateTime::now(NF_TimeZone::utc());

        $this->assertFalse(NF_TimeZone::isUtc($default->getTimezone()));
        $this->assertFalse(NF_TimeZone::isUtc($local->getTimezone()));
        $this->assertTrue(NF_TimeZone::isUtc($utc->getTimezone()));
    }

    public function testLocal()
    {
        $tz = NF_TimeZone::local();
        $this->assertEquals(date_default_timezone_get(), $tz->getName());
    }

    public function testUtc()
    {
        $tz = NF_TimeZone::utc();
        $this->assertEquals('UTC', $tz->getName());
    }

    public function testSetLocal()
    {
        $default = date_default_timezone_get();

        NF_TimeZone::setLocal('Europe/Berlin');
        $this->assertEquals('Europe/Berlin', NF_TimeZone::local()->getName());

        NF_TimeZone::setLocal('America/New_York');
        $this->assertEquals('America/New_York', NF_TimeZone::local()->getName());

        date_default_timezone_set('Asia/Tokyo');
        $this->assertEquals('Asia/Tokyo', NF_TimeZone::local()->getName());

        NF_TimeZone::setLocal($default);
    }
}
