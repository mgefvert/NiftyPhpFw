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
    /** @persist-type int          */ public $id;
    /** @persist-type int          */ public $entry;
    /** @persist-type datetime-utc */ public $created;
    /** @persist-type string       */ public $ip;
    /** @persist-type string       */ public $text;
    /** @persist-type string       */ public $signed;
    /** @persist-type string       */ public $email;

    public static function loadComments($page, $pageCount)
    {
        if ($page < 0)
            $page = 0;

        $start = $page * $pageCount;
        $limit = $pageCount;

        return NF::persist()->loadByWhereClause(__CLASS__, "order by [id] desc limit $start, $limit");
    }

    public static function getCommentCount()
    {
        return NF::db()->queryScalar('select count(*) from blog_comments');
    }
}
