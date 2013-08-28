<?php

/**
 * Handle post editing. See the Admin_Users class for more comments.
 */
class Admin_Posts extends AdminPage
{
    public function executeView($pg = 0)
    {
        $entries = Data_BlogEntry::loadEntries($pg, 20, false);
        NF::persist()->loadRelated($entries, 'objUser');

        NF::response()->content = NF_Template::runDefault(null, array(
            'entries' => $entries
        ));
    }

    public function executeEdit($id = null)
    {
        $iv = new NF_IV();
        $entry = $id ? NF::persist()->load('Data_BlogEntry', $id) : new Data_BlogEntry();

        if (NF::request()->isGet())
        {
            // Require nicEdit. This will be inserted into the main.phtml template
            // through the <@js> expansion.
            NF::response()->requireJs('/blog/js/nicEdit.js');
            NF::response()->content = NF_Template::runDefault(null, array(
                'entry' => $entry,
                'form'  => $iv->inject()
            ));
        }
        else if (NF::request()->isPost())
        {
            $entry->created = NF_DateTime::now();
            $entry->title   = trim(NF::request()->title);
            $entry->text    = trim(NF::request()->text);
            $entry->user    = NF::session()->uid;

            if ($entry->id)
                NF::persist()->save($entry);
            else
                $id = NF::persist()->insert($entry);

            NF::response()->slowRedirect($this->ref('view', null, array('lastpost' => $id)));
        }
    }

    public function executeDelete($id)
    {
        $id = (int)$id;

        NF::db()->execute("delete from blog_entries where b_id=$id");
        NF::db()->execute("delete from blog_comments where bc_entry=$id");
    }
}
