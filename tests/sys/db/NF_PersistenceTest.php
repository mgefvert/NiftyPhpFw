<?php

require_once 'Data.php';

class NF_PersistenceTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $db = NF::db();
        $db->execute('truncate test');
    }

    protected function tearDown()
    {
        $db = NF::db();
    }

    public function testSinglePersistence()
    {
        $db = NF::db();
        $persist = NF::persist($db);

        // Test insertion
        $obj = new Test();
        $obj->id = 1;
        $obj->text = 'hello';
        $obj->reqd = 2;
        $persist->insert($obj);

        $obj->id = 2;
        $obj->text = 'hello again';
        $obj->reqd = 8;
        $persist->insert($obj);

        unset($obj);
        $this->assertFalse(isset($obj));

        $this->assertEquals(2, $db->queryScalar('select count(*) from test'));

        // Test direct loading
        $obj = $persist->load('Test', 1);
        $this->assertInstanceOf('Test', $obj);
        $this->assertEquals(1, $obj->id);
        $this->assertEquals('hello', $obj->text);
        $this->assertEquals(2, $obj->reqd);
        $this->assertEquals(null, $obj->opt);

        // Test updating
        $obj->opt = 76;
        $persist->save($obj);
        unset($obj);

        $obj = $persist->load('Test', 1);
        $this->assertEquals(1, $obj->id);
        $this->assertEquals('hello', $obj->text);
        $this->assertEquals(2, $obj->reqd);
        $this->assertEquals(76, $obj->opt);

        // Test custom queries
        $q = $persist->loadAll('Test');
        $this->assertEquals(2, count($q));
        foreach($q as $item)
            $this->assertInstanceOf('Test', $item);

        $q = $persist->loadByWhereClause('Test', 'where id = 1');
        $this->assertEquals(1, count($q));
        $this->assertInstanceOf('Test', $q[0]);

        $q = $persist->loadByQuery('Test', 'select * from test where id <= 1');
        $this->assertEquals(1, count($q));
        $this->assertEquals(1, $q[0]->id);

        // Test object deletion
        $persist->deleteObject($obj);
        $this->assertEquals(1, $db->queryScalar('select count(*) from test'));

        // Test direct deletion
        $persist->delete('Test', 2);
        $this->assertEquals(0, $db->queryScalar('select count(*) from test'));
    }

    public function testSinglePersistenceMap()
    {
        $db = NF::db();
        $persist = NF::persist($db);

        // Test insertion
        $obj = new TestMap();
        $obj->m_id = 1;
        $obj->m_text = 'hello';
        $obj->m_reqd = 2;
        $persist->insert($obj);

        $obj->m_id = 2;
        $obj->m_text = 'hello again';
        $obj->m_reqd = 8;
        $persist->insert($obj);

        unset($obj);
        $this->assertFalse(isset($obj));

        $this->assertEquals(2, $db->queryScalar('select count(*) from test'));

        // Test direct loading
        $obj = $persist->load('TestMap', 1);
        $this->assertInstanceOf('TestMap', $obj);
        $this->assertEquals(1, $obj->m_id);
        $this->assertEquals('hello', $obj->m_text);
        $this->assertEquals(2, $obj->m_reqd);
        $this->assertEquals(null, $obj->m_opt);

        // Test updating
        $obj->m_opt = 76;
        $persist->save($obj);
        unset($obj);

        $obj = $persist->load('TestMap', 1);
        $this->assertEquals(1, $obj->m_id);
        $this->assertEquals('hello', $obj->m_text);
        $this->assertEquals(2, $obj->m_reqd);
        $this->assertEquals(76, $obj->m_opt);

        // Test custom queries
        $q = $persist->loadAll('TestMap');
        $this->assertEquals(2, count($q));
        foreach($q as $item)
            $this->assertInstanceOf('TestMap', $item);

        $q = $persist->loadByWhereClause('TestMap', 'where [m_id] = 1');
        $this->assertEquals(1, count($q));
        $this->assertInstanceOf('TestMap', $q[0]);

        $q = $persist->loadByQuery('TestMap', 'select * from [:TestMap] where [m_id:TestMap] <= 1');
        $this->assertEquals(1, count($q));
        $this->assertEquals(1, $q[0]->m_id);

        // Test object deletion
        $persist->deleteObject($obj);
        $this->assertEquals(1, $db->queryScalar('select count(*) from test'));

        // Test direct deletion
        $persist->delete('TestMap', 2);
        $this->assertEquals(0, $db->queryScalar('select count(*) from test'));
    }

    public function testSingleFaultyUpdate()
    {
        $db = NF::db();
        $persist = NF::persist($db);

        $this->setExpectedException('NF_EDatabaseError');

        $obj = new Test();
        $persist->save($obj);
    }

    public function testSingleFaultyDeleteObject()
    {
        $db = NF::db();
        $persist = NF::persist($db);

        $this->setExpectedException('NF_EDatabaseError');

        $obj = new Test();
        $persist->deleteObject($obj);
    }
}
