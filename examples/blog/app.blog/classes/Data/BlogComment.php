<?php

NF_Persistence::mapTable('Data_BlogComment', 'blog_comments', 'id');
NF_Persistence::mapFields('Data_BlogComment', array(
    'id'      => 'bc_id',
    'entry'   => 'bc_entry',
    'created' => 'bc_createdUTC',
    'ip'      => 'bc_ip',
    'text'    => 'bc_text',
    'signed'  => 'bc_signed',
    'email'   => 'bc_email'
));

NF_Persistence::mapRelationM1('Data_BlogComment', 'Data_BlogEntry', 'entry', 'id', 'objEntry');

/**
 * Class that encapsulates a comment. See Data_BlogEntry for more description.
 */
class Data_BlogComment
{
    public $id;
    public $entry;
    public $created;
    public $ip;
    public $text;
    public $signed;
    public $email;

    public function loaded()
    {
        $this->created = new NF_DateTime($this->created);
        $this->created->setTZ('UTC');
    }

    public function persist()
    {
        $this->created->adjustTZ('UTC');
    }

    public static function loadComments($page, $pageCount)
    {
        global $Persistence;

        if ($page < 0)
            $page = 0;

        $start = $page * $pageCount;
        $limit = $pageCount;

        return $Persistence->loadByWhereClause(__CLASS__, "order by [id] desc limit $start, $limit");
    }

    public static function getCommentCount()
    {
        return NF_DB::connect()->queryScalar('select count(*) from blog_comments');
    }
}
