<?php

require_once 'Data.php';

NF_Persistence::mapTable('TestDiscriminator', 'test_discr', array('idtype', 'id'));
NF_Persistence::mapRelation1M('TestDiscriminator', 'Test', 'id', 'id', 'objTest')->addSourceDiscriminator('idtype', 1);
NF_Persistence::mapRelation1M('Test', 'TestDiscriminator', 'reqd', 'id', 'objTest')->addTargetDiscriminator('idtype', 1);

class TestDiscriminator
{
    public $idtype;
    public $id;
    public $text;
}

class NF_Persistence_DiscriminatorTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        NF::db()->execute('drop table if exists test_discr');
        NF::db()->execute('
            create table test_discr (
                idtype char(1) not null,
                id integer not null,
                text varchar(80)
            )
        ');
    }

    public static function tearDownAfterClass()
    {
        NF::db()->execute('drop table if exists test_discr');
    }

    protected function setUp()
    {
        NF::db()->execute('truncate test');
        NF::db()->execute('truncate test_discr');
    }

    public function testSourceDiscriminator()
    {
        $db = NF::db();
        $persist = NF::persist($db);

        $db->execute('insert into test values (1, "Test1", 1, null);');
        $db->execute('insert into test values (2, "Test2", 2, null);');

        $db->execute('insert into test_discr values (1, 1, "Discr1");');
        $db->execute('insert into test_discr values (1, 2, "Discr2");');
        $db->execute('insert into test_discr values (1, 3, "Discr3");');
        $db->execute('insert into test_discr values (2, 1, "Other1");');
        $db->execute('insert into test_discr values (2, 2, "Other2");');
        $db->execute('insert into test_discr values (2, 3, "Other32");');

        $obj = $persist->load('Test', 2);
        $result = $persist->loadRelated($obj, 'TestDiscriminator');

        $this->assertEquals(1, count($result));
        $this->assertEquals('Discr2', $result[0]->text);
    }

    public function testTargetDiscriminator()
    {
        $db = NF::db();
        $persist = NF::persist($db);

        $db->execute('insert into test values (10, "Test1", 1, null);');
        $db->execute('insert into test values (20, "Test2", 2, null);');

        $db->execute('insert into test_discr values (1, 10, "Discr1");');
        $db->execute('insert into test_discr values (2, 20, "Other2");');

        $obj = $persist->loadAll('TestDiscriminator');
        $this->assertEquals(2, count($obj));

        $result = $persist->loadRelated($obj, 'Test');
        $this->assertEquals(1, count($result));

        $result = array_shift($result);
        $this->assertEquals(10, $result->id);
    }
}
