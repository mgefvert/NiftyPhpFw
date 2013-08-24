<?php

/**
 * Handle post editing. See the Admin_Users class for more comments.
 */
class Admin_Posts extends AdminPage
{
    public function executeView($pg = 0)
    {
        global $Response, $Persistence;

        $entries = Data_BlogEntry::loadEntries($pg, 20, false);
        $Persistence->loadRelated($entries, 'objUser');

        $Response->content = NF_Template::runDefault(null, array(
            'entries' => $entries
        ));
    }

    public function executeEdit($id = null)
    {
        global $Persistence, $Response, $Request, $Session;

        $formHelper = new NF_FormHelper();

        $entry = $id ? $Persistence->load('Data_BlogEntry', $id) : new Data_BlogEntry();

        if ($Request->isGet())
        {
            // Require nicEdit. This will be inserted into the main.phtml template
            // through the <@js> expansion.
            $Response->requireJs('/js/nicEdit.js');
            $Response->content = NF_Template::runDefault(null, array(
                'entry' => $entry,
                'form'  => $formHelper->inject()
            ));
        }
        else if ($Request->isPost())
        {
            $entry->created = NF_DateTime::now();
            $entry->title   = trim($Request->title);
            $entry->text    = trim($Request->text);
            $entry->user    = $Session->uid;

            if ($entry->id)
                $Persistence->save($entry);
            else
                $id = $Persistence->insert($entry);

            $Response->slowRedirect($this->ref('view', null, array('lastpost' => $id)));
        }
    }

    public function executeDelete($id)
    {
        NF_Filter::fint($id);

        NF_DB::connect()->execute("delete from blog_entries where b_id=$id");
        NF_DB::connect()->execute("delete from blog_comments where bc_entry=$id");
    }
}
