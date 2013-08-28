<?php

/**
 * Handle comment editing. See the Admin_Users class for more comments.
 */
class Admin_Comments extends AdminPage
{
    public function executeView($pg = 0)
    {
        $comments = Data_BlogComment::loadComments($pg, 20);
        NF::persist()->loadRelated($comments, 'objEntry');

        NF::response()->content = NF_Template::runDefault(null, array(
            'comments' => $comments
        ));
    }

    public function executeDelete($id)
    {
        NF_Filter::fint($id);

        NF::db()->execute("delete from blog_comments where bc_id=$id");
    }
}
