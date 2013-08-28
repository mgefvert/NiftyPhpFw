<?php

/**
 * The main admin page just shows a bunch of statistics and a menu. Nothing
 * fancy.
 *
 * Make sure to notice that it derives from AdminPage instead of NF_Page. AdminPage
 * it turn derives from NF_AuthPage, which checks if the user is authenticated
 * (= has a $Session->uid value). If not we will throw a 304 Access Denied upon
 * loading the class. This effectively provides security for all administration pages.
 */
class Admin_Main extends AdminPage
{
    public function executeView()
    {
        // We use NF::db() here instead of going through NF::persist().
        // Just for convenience. Sometimes it just becomes too much, right?
        $statistics = array_shift(NF::db()->queryAsObjects("
            select
                (select count(*) from blog_entries) as totalEntries,
                (select count(*) from blog_entries where b_createdUTC >= date_sub(curdate(), interval 1 month)) as monthEntries,
                (select count(*) from blog_comments) as totalComments,
                (select count(*) from blog_comments where bc_createdUTC >= date_sub(curdate(), interval 1 month)) as monthComments,
                (select count(*) from users) as totalUsers
        "));

        NF::response()->content = NF_Template::runDefault(null, array(
            'statistics' => $statistics
        ));
    }
}
