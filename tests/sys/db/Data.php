<?php

NF_Persistence::mapTable('Test', 'test', 'id');
class Test
{
    public $id;
    public $text;
    public $reqd;
    public $opt;
}

NF_Persistence::mapTable('TestMap', 'test', 'm_id');
NF_Persistence::mapFields('TestMap', array(
    'm_id'   => 'id',
    'm_text' => 'text',
    'm_reqd' => 'reqd',
    'm_opt'  => 'opt',
));

class TestMap
{
    public $m_id;
    public $m_text;
    public $m_reqd;
    public $m_opt;
}

