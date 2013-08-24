<?php

class NF_ArraysTest extends PHPUnit_Framework_TestCase
{
    public function testObjectsFromArrays()
    {
        $x = array(
            array('id' => 1, 'data' => 'Påsk'),
            array('id' => 2, 'data' => 'Jul'),
            array('id' => 3, 'data' => 5.9)
        );

        $x = NF_Arrays::objectsFromArrays($x);

        $this->assertEquals(3, count($x));
        $this->assertTrue(is_array($x));

        $obj = array_shift($x);
        $this->assertTrue(is_object($obj));
        $this->assertEquals('stdClass', get_class($obj));
        $this->assertEquals(1, $obj->id);
        $this->assertEquals('Påsk', $obj->data);

        $obj = array_shift($x);
        $this->assertTrue(is_object($obj));
        $this->assertEquals('stdClass', get_class($obj));
        $this->assertEquals(2, $obj->id);
        $this->assertEquals('Jul', $obj->data);

        $obj = array_shift($x);
        $this->assertTrue(is_object($obj));
        $this->assertEquals('stdClass', get_class($obj));
        $this->assertEquals(3, $obj->id);
        $this->assertEquals(5.9, $obj->data);
    }

    public function testHash()
    {
        $x = array(
            array('id' => 5,   'text' => 'string-5'),
            array('id' => 10,  'text' => 'string-10'),
            array('id' => 97,  'text' => 'string-97'),
            array('id' => 102, 'text' => 'string-102'),
        );

        $x = NF_Arrays::hash($x, 'id');

        $this->assertEquals(4, count($x));
        $this->assertEquals(array(5, 10, 97, 102), array_keys($x));
        $this->assertEquals(102, $x[102]['id']);
        $this->assertEquals('string-97', $x[97]['text']);

        $x = array();

        $obj = new stdClass();
        $obj->id = 3;
        $obj->text = 'string-3';
        $x[] = $obj;

        $obj = new stdClass();
        $obj->id = 8;
        $obj->text = 'string-8';
        $x[] = $obj;

        $obj = new stdClass();
        $obj->id = 16;
        $obj->text = 'string-16';
        $x[] = $obj;

        $x = NF_Arrays::hash($x, 'text');

        $this->assertEquals(3, count($x));
        $this->assertEquals(array('string-3', 'string-8', 'string-16'), array_keys($x));
        $this->assertEquals(8, $x['string-8']->id);
        $this->assertEquals('string-16', $x['string-16']->text);
    }

    public function testGroup()
    {
        $x = array(
            array('id' => 5, 'text' => 'string-5'),
            array('id' => 5, 'text' => 'string-5'),
            array('id' => 6, 'text' => 'string-6'),
        );

        $x = NF_Arrays::group($x, 'id');

        $this->assertEquals(2, count($x));
        $this->assertEquals(2, count($x[5]));
        $this->assertEquals(1, count($x[6]));
    }

    public function testAccess()
    {
        $x = new stdClass();
        $x->item = new stdClass();
        $x->item->subitem = new stdClass();
        $x->item->subitem->text = 'Fnork';

        $this->assertEquals(null, NF_Arrays::access($x, 'text'));
        $this->assertEquals(null, NF_Arrays::access($x, 'item->text'));
        $this->assertEquals('Fnork', NF_Arrays::access($x, 'item->subitem->text'));
        $this->assertEquals(null, NF_Arrays::access($x, 'item->subitem->text->pass'));

        $this->assertEquals(1, NF_Arrays::access($x, 'text', 1));
        $this->assertEquals(1, NF_Arrays::access($x, 'item->text', 1));
        $this->assertEquals('Fnork', NF_Arrays::access($x, 'item->subitem->text', 1));
        $this->assertEquals(1, NF_Arrays::access($x, 'item->subitem->text->pass', 1));
    }

    public function testSqlAnd()
    {
        $this->assertEquals('', NF_Arrays::sql_and(array()));
        $this->assertEquals('((x=1))', NF_Arrays::sql_and(array('x=1')));
        $this->assertEquals('((x=1) and (x=2) and (x=3))', NF_Arrays::sql_and(array('x=1', 'x=2', 'x=3')));
    }

    public function testSqlOr()
    {
        $this->assertEquals('', NF_Arrays::sql_or(array()));
        $this->assertEquals('((x=1))', NF_Arrays::sql_or(array('x=1')));
        $this->assertEquals('((x=1) or (x=2) or (x=3))', NF_Arrays::sql_or(array('x=1', 'x=2', 'x=3')));
    }
}
