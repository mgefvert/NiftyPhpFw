<?php

NF_Persistence::mapTable('TestFT', 'test_ft', 'id');

class TestFT
{
    /** @persist-type int */       public $id;
    /** @persist-type int */       public $int;
    /** @persist-type string */    public $str;
    /** @persist-type float */     public $temp;
    /** @persist-type date */      public $date;
    /** @persist-type time */      public $time;
                                   public $dt;
    /** @persist-type timestamp */ public $ts;
}

class NF_Persistence_FieldTypeTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        NF::db()->execute('drop table if exists test_ft');
        NF::db()->execute('
            create table test_ft (
                `id` integer not null primary key,
                `int` integer,
                `str` varchar(40),
                `temp` double,
                `date` date,
                `time` time,
                `dt` datetime,
                `ts` integer
            )
        ');
    }

    public static function tearDownAfterClass()
    {
        NF::db()->execute('drop table if exists test_ft');
    }

    protected function setUp()
    {
        NF::db()->execute('truncate test_ft');
    }

    public function testFieldTypes()
    {
        $obj = new TestFT();
        $obj->id = '1';
        $obj->int = '2';
        $obj->str = 3;
        $obj->temp = '1.5';
        $obj->date = '2010-01-01';
        $obj->time = '13:05:45';
        $obj->ts = '2013-12-24 18:00:00';
        NF::persist()->insert($obj);

        $obj = NF::persist()->load('TestFT', 1);
        $this->assertSame(1, $obj->id);
        $this->assertSame(2, $obj->int);
        $this->assertSame('3', $obj->str);
        $this->assertSame(1.5, $obj->temp);
        $this->assertEquals(new NF_Date('2010-01-01'), $obj->date);
        $this->assertEquals(new NF_Time('13:05:45'), $obj->time);
        $this->assertEquals(new NF_DateTime('2013-12-24 18:00:00'), $obj->ts);

        $obj->int = 12;
        $obj->str = 13;
        $obj->temp = '41.5';
        $obj->date = '2010-01-15';
        $obj->time = '16:05:45';
        $obj->ts = '2013-12-25 14:30:00';
        NF::persist()->save($obj);

        $obj = NF::persist()->load('TestFT', 1);
        $this->assertSame(1, $obj->id);
        $this->assertSame(12, $obj->int);
        $this->assertSame('13', $obj->str);
        $this->assertSame(41.5, $obj->temp);
        $this->assertEquals(new NF_Date('2010-01-15'), $obj->date);
        $this->assertEquals(new NF_Time('16:05:45'), $obj->time);
        $this->assertEquals(new NF_DateTime('2013-12-25 14:30:00'), $obj->ts);
    }

    public function testFieldTypesNull()
    {
        $obj = new TestFT();
        $obj->id = 2;
        NF::persist()->insert($obj);

        $obj = NF::persist()->load('TestFT', 2);
        $this->assertSame(2, $obj->id);
        $this->assertSame(null, $obj->int);
        $this->assertSame(null, $obj->str);
        $this->assertSame(null, $obj->temp);
        $this->assertEquals(new NF_Date(), $obj->date);
        $this->assertEquals(new NF_Time(), $obj->time);
        $this->assertEquals(new NF_DateTime(), $obj->ts);

        NF::persist()->save($obj);

        $obj = NF::persist()->load('TestFT', 2);
        $this->assertSame(2, $obj->id);
        $this->assertSame(null, $obj->int);
        $this->assertSame(null, $obj->str);
        $this->assertSame(null, $obj->temp);
        $this->assertEquals(new NF_Date(), $obj->date);
        $this->assertEquals(new NF_Time(), $obj->time);
        $this->assertEquals(new NF_DateTime(), $obj->ts);
    }

    public function testPersistDatetime()
    {
        NF_Persistence::setFieldType('TestFT', 'datetime', array('dt'));

        $obj = new TestFT();
        $obj->id = 42;
        $obj->dt = new NF_DateTime('2010-01-01 12:00');
        NF::persist()->insert($obj);

        $this->assertTrue(NF_TimeZone::isLocal($obj->dt->getTimezone()));
        $this->assertSame('2010-01-01T12:00:00+01:00', $obj->dt->format('c'));
        $this->assertSame('2010-01-01 12:00:00', NF::db()->queryScalar('select dt from test_ft'));

        $obj = NF::persist()->load('TestFT', 42);
        $this->assertTrue(NF_TimeZone::isLocal($obj->dt->getTimezone()));
        $this->assertSame('2010-01-01T12:00:00+01:00', $obj->dt->format('c'));

        $obj = new TestFT();
        $obj->id = 42;
        $obj->dt = new NF_DateTime('2010-01-01 12:00');
        NF::persist()->save($obj);
        $this->assertSame('2010-01-01 12:00:00', NF::db()->queryScalar('select dt from test_ft'));
    }

    public function testPersistDatetimeUtc()
    {
        NF_Persistence::setFieldType('TestFT', 'datetime-utc', array('dt'));

        $obj = new TestFT();
        $obj->id = 43;
        $obj->dt = new NF_DateTime('2010-01-01 12:00');
        $this->assertTrue(NF_TimeZone::isLocal($obj->dt->getTimezone()));
        NF::persist()->insert($obj);

        $this->assertTrue(NF_TimeZone::isLocal($obj->dt->getTimezone()));
        $this->assertSame('2010-01-01T12:00:00+01:00', $obj->dt->format('c'));
        $this->assertSame('2010-01-01 11:00:00', NF::db()->queryScalar('select dt from test_ft'));

        $obj = NF::persist()->load('TestFT', 43);
        $this->assertTrue(NF_TimeZone::isLocal($obj->dt->getTimezone()));
        $this->assertSame('2010-01-01T12:00:00+01:00', $obj->dt->format('c'));

        $obj = new TestFT();
        $obj->id = 43;
        $obj->dt = new NF_DateTime('2010-01-01 12:00');
        NF::persist()->save($obj);
        $this->assertSame('2010-01-01 11:00:00', NF::db()->queryScalar('select dt from test_ft'));
    }

    public function testPersistDatetimeUtcFixed()
    {
        NF_Persistence::setFieldType('TestFT', 'datetime-utc-fixed', array('dt'));

        $obj = new TestFT();
        $obj->id = 44;
        $obj->dt = new NF_DateTime('2010-01-01 12:00');
        $this->assertTrue(NF_TimeZone::isLocal($obj->dt->getTimezone()));
        NF::persist()->insert($obj);

        $this->assertTrue(NF_TimeZone::isLocal($obj->dt->getTimezone()));
        $this->assertSame('2010-01-01T12:00:00+01:00', $obj->dt->format('c'));
        $this->assertSame('2010-01-01 11:00:00', NF::db()->queryScalar('select dt from test_ft'));

        $obj = NF::persist()->load('TestFT', 44);
        $this->assertTrue(NF_TimeZone::isUtc($obj->dt->getTimezone()));
        $this->assertSame('2010-01-01T11:00:00+00:00', $obj->dt->format('c'));

        $obj = new TestFT();
        $obj->id = 44;
        $obj->dt = new NF_DateTime('2010-01-01 12:00');
        NF::persist()->save($obj);
        $this->assertSame('2010-01-01 11:00:00', NF::db()->queryScalar('select dt from test_ft'));
    }

    public function testIllegalFieldType()
    {
        $this->setExpectedException('NF_EDatabaseError');

        NF_Persistence::setFieldType('TestFT', 'datetime-fail', array('dt'));
    }
}
