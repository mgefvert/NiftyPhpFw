<?php

class NF_DbTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $db = NF::db();
        $db->execute('truncate test');
    }

    public function testConnect()
    {
        $db = NF::db();

        $tables = $db->queryAsArray('show tables');
        $this->assertEquals(1, count($tables));

        $this->assertTrue($db->isOpen());
        $db->close();
        $this->assertFalse($db->isOpen());

        $db = NF::db();
        $this->assertTrue($db->isOpen());
    }

    public function testCloseAll()
    {
        $db = NF::db();

        $this->assertTrue($db->isOpen());
        NF::closeAllDatabases();
        $this->assertFalse($db->isOpen());

        $db = NF::db();
        $this->assertTrue($db->isOpen());
    }

    public function testPopulateAndQuery()
    {
        $db = NF::db(NF::config()->getDatabaseConnection());

        $db->execute('insert into test (id, reqd) values (1, 1001), (2, 1002), (3, 1003), (4, 1004), (5, 1005)');

        // Test query
        $q = $db->query('select id from test where id <= 2 order by id');
        $this->assertTrue($q instanceof NF_IResult);

        $this->assertTrue(($r = $q->fetch()) !== false);
        $this->assertEquals(1, count($r));
        $this->assertEquals(1, $r['id']);

        $this->assertTrue(($r = $q->fetch()) !== false);
        $this->assertEquals(1, count($r));
        $this->assertEquals(2, $r['id']);

        $this->assertTrue($q->fetch() === false);

        // Test queryScalar
        $this->assertEquals(5, (int) $db->queryScalar('select count(*) from test'));

        // Test queryAllAsArray
        $q = $db->queryAsArray('select id from test where id <= 2 order by id');
        $this->assertEquals(2, count($q));
        $this->assertEquals(1, $q[0]['id']);
        $this->assertEquals(2, $q[1]['id']);

        // Test querySingleValueArray
        $q = $db->querySingleValueArray('select id from test order by id');
        $this->assertEquals(array(1, 2, 3, 4, 5), $q);

        // Test queryLookup
        $q = $db->queryLookup('select id, reqd from test where id <= 3 order by id');
        $this->assertEquals(array(1 => 1001, 2 => 1002, 3 => 1003), $q);

        // Test lastInsertId
        $db->execute('insert into test (reqd) values (1006)');
        $this->assertEquals(6, $db->lastInsertId());
    }

    public function testEscaping()
    {
        $db = NF::db(NF::config()->getDatabaseConnection());

        $data = 'barney';
        $db->execute('insert into test (reqd, text) values (1, ' . $db->quote($data) . ')');
        $this->assertEquals($data, $db->queryScalar('select text from test'));
        $db->execute('delete from test');

        $data = '"barney\'s test';
        $db->execute('insert into test (reqd, text) values (1, ' . $db->quote($data) . ')');
        $this->assertEquals($data, $db->queryScalar('select text from test'));
        $db->execute('delete from test');
    }
}
