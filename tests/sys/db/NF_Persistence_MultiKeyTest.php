<?php

require_once 'Data.php';

NF_Persistence::mapTable('TestMk', 'test_mk', array('key1', 'key2'));
NF_Persistence::mapTable('TestMkMap', 'test_mk', array('m_key1', 'm_key2'));
NF_Persistence::mapFields('TestMkMap', array(
    'm_key1' => 'key1',
    'm_key2' => 'key2',
    'm_text' => 'text'
));

class TestMk
{
    public $key1;
    public $key2;
    public $text;
}

class TestMkMap
{
    public $m_key1;
    public $m_key2;
    public $m_text;
}

class NF_Persistence_MultiKeyTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        NF::db()->execute('drop table if exists test_mk');
        NF::db()->execute('
            create table test_mk (
                key1 integer not null,
                key2 integer not null,
                text varchar(80),
                primary key (key1, key2)
            )
        ');
    }

    public static function tearDownAfterClass()
    {
        NF::db()->execute('drop table if exists test_mk');
    }

    protected function setUp()
    {
        NF::db()->execute('truncate test_mk');
    }

    public function testMultiKeyPersistence()
    {
        $db = NF::db();
        $persist = NF::persist($db);

        // Test insertion
        $obj = new TestMk();
        $obj->key1 = 1;
        $obj->key2 = 10;
        $obj->text = 'hello';
        $persist->insert($obj);

        $obj->key1 = 1;
        $obj->key2 = 20;
        $obj->text = 'hello again';
        $persist->insert($obj);

        unset($obj);
        $this->assertFalse(isset($obj));

        $this->assertEquals(2, $db->queryScalar('select count(*) from test_mk'));

        // Test direct loading
        $obj = $persist->load('TestMk', array(1, 10));
        $this->assertInstanceOf('TestMk', $obj);
        $this->assertEquals(1, $obj->key1);
        $this->assertEquals(10, $obj->key2);
        $this->assertEquals('hello', $obj->text);

        // Test updating
        $obj->text = 'bork';
        $persist->save($obj);
        unset($obj);

        $obj = $persist->load('TestMk', array(1, 10));
        $this->assertEquals(1, $obj->key1);
        $this->assertEquals(10, $obj->key2);
        $this->assertEquals('bork', $obj->text);

        // Test custom queries
        $q = $persist->loadAll('TestMk');
        $this->assertEquals(2, count($q));
        foreach($q as $item)
            $this->assertInstanceOf('TestMk', $item);

        $q = $persist->loadByQuery('TestMk', 'select * from test_mk where key2 = 20');
        $this->assertEquals(1, count($q));
        $this->assertEquals(1, $q[0]->key1);
        $this->assertEquals(20, $q[0]->key2);
        $this->assertInstanceOf('TestMk', $q[0]);

        // Test object deletion
        $obj->key2 = 20;
        $persist->deleteObject($obj);
        $this->assertEquals(1, $db->queryScalar('select count(*) from test_mk'));

        // Test direct deletion
        $persist->delete('TestMk', array(1, 10));
        $this->assertEquals(0, $db->queryScalar('select count(*) from test_mk'));
    }

    public function testMultiFaultyUpdate()
    {
        $this->setExpectedException('NF_EDatabaseError');

        $obj = new TestMk();
        NF::persist()->save($obj);
    }

    public function testMultiFaultyDeleteObject()
    {
        $this->setExpectedException('NF_EDatabaseError');

        $obj = new TestMk();
        NF::persist()->deleteObject($obj);
    }
}
