<?php

// These calls maps the class to a table and specifies the index field.
NF_Persistence::mapTable('Data_BlogEntry', 'blog_entries', 'id');
NF_Persistence::mapFields('Data_BlogEntry', array(
    'id'      => 'b_id',
    'created' => 'b_createdUTC',
    'title'   => 'b_title',
    'text'    => 'b_text',
    'user'    => 'b_user'
));

// These calls maps relations between tables. We can use these with $Persistence->loadRelated later.
NF_Persistence::mapRelation1M('Data_BlogEntry', 'Data_BlogComment', 'id', 'entry', 'objComments');
NF_Persistence::mapRelationM1('Data_BlogEntry', 'Data_User', 'user', 'id', 'objUser');

/**
 * Contains a single blog entry.
 */
class Data_BlogEntry
{
    /** @persist-type int          */ public $id;
    /** @persist-type datetime-utc */ public $created;
    /** @persist-type string       */ public $title;
    /** @persist-type string       */ public $text;
    /** @persist-type int          */ public $user;

    /**
     * getMaxPubDate() fetches the last publication date of all articles.
     * Used for the RSS feed.
     *
     * @return NF_DateTime
     */
    public static function getMaxPubDate()
    {
        $date = NF::persist()->queryScalar(__CLASS__, 'select max([created]) from [:Data_BlogEntry]');
        if ($date)
        {
            $date = new NF_DateTime($date, NF_TimeZone::utc());
            $date->setTimezone(NF_TimeZone::local());
        }

        return $date;
    }

    /**
     * loadEntries loads a specified number of entries, with or without text.
     * It also loads an overview of the number of comments for each article,
     * for easy reference. Note that this is stored as the variable "comments",
     * but since we don't declare that variable in the class, NF_Persistence
     * won't try to save it - it's a "ghost" variable.
     *
     * @param int $page
     * @param int $pageCount
     * @param bool $includeText
     * @return array
     */
    public static function loadEntries($page, $pageCount, $includeText)
    {
        if ($page < 0)
            $page = 0;

        $start = $page * $pageCount;
        $limit = $pageCount;

        if ($includeText)
            $fields = '*';  // Load all fields - NF_Persistence takes care of field mapping
        else
        {
            // Load all fields except for "text", which just takes up lots of space if
            // we don't need it. Find all class variables, exlude "text", and make a field list.
            $fields = get_class_vars(__CLASS__);
            unset($fields['text']);
            $fields = '[' . implode('], [', array_keys($fields)) . ']';
        }

        return NF::persist()->loadByQuery(__CLASS__, "
            select $fields,
                (select count(*) from [:Data_BlogComment] where [entry:Data_BlogComment]=[id]) as comments
            from [:Data_BlogEntry]
            order by [id] desc
            limit $start, $limit
        ");
    }

    /**
     * Get the total post count.
     *
     * @return int
     */
    public static function getPostCount()
    {
        return NF::db()->queryScalar('select count(*) from blog_entries');
    }
}
