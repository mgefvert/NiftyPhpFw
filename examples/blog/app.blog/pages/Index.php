<?php

/**
 * This is the Index page. Whenever we access the site without any specific
 * URL (e.g. just http://localhost/), this is what is loaded.
 *
 * It descends from NF_Page, since everyone is allowed to access.
 */
class Index extends NF_Page
{
    /**
     * NF_FormHelper is a cool little function that provides us with form
     * storage. Normally, we use it to prevent XSRF attacks - whenever any
     * POST call is made, NF_FormHelper checks to see if it belongs to a
     * valid session by looking at the injected <input type="hidden"> values.
     *
     * @var NF_FormHelper
     */
    protected $iv;

    /**
     * init() gets called before any execute... function is called.
     */
    protected function init()
    {
        parent::init();

        // Instantiating formHelper here means that every POST call to this
        // page must have formHelper data with it, otherwise we never even
        // reach the execute... function.
        $this->iv = new NF_IV();
    }

    // The main executeView() call. The default call. It takes a $pg parameter
    // for pagination on the front page.
    public function executeView($pg = 0)
    {
        // Load the blog entries.
        $entries = Data_BlogEntry::loadEntries($pg, 10, true);

        // Send the blog entries through the index/view.phtml page, which in
        // turn gets send to the main.phtml page when we return from this call.
        NF::response()->content = NF_Template::runDefault(null, array(
            'entries' => $entries
        ));
    }

    // View a specific blog post, e.g. /index/view/1 ($id = 1)
    // If we GET, we view the item. If we POST, we post a comment.
    // Remember that formHelper is active and checking POSTs to make
    // sure that we have valid session keys.
    public function executeItem($id)
    {
        if (NF::request()->isGet())
        {
            // View the entry. Try to load it - if we don't find it, we give
            // a 404 error. This is nicer than using $Persistence->load, and
            // failing with a database error if we don't find the entry.
            $entry = NF::persist()->tryLoad('Data_BlogEntry', $id);
            if (!$entry)
                throw new NF_EPageNotFound();

            // Load the related comments. They become available as $entry->objComments,
            // since that's how it was defined in NF_Persistence::mapRelation1M for
            // Data_BlogEntry ($entry is a Data_BlogEntry).
            NF::persist()->loadRelated($entry, 'objComments');

            // Parse the entry, also inject the FormHelper data that allows
            // us to validate POSTs for session consistency. (Might cut down on spam?)
            NF::response()->content = NF_Template::runDefault(null, array(
                'entry' => $entry,
                'form'  => $this->iv->inject()
            ));
        }
        else if (NF::request()->isPost())
        {
            // A comment was posted from the form. Get the data.
            // We could have included $name, $email and $text in the
            // executeItem() parameter list, of course, but that would
            // affect the GET call, too, so this is more intuitive.
            $name = trim(NF::request()->name);
            $email = trim(NF::request()->email);
            $text = trim(NF::request()->text);

            if ($name && $email && $text)
            {
                // Create a new Data_BlogComment instance.
                $comment = new Data_BlogComment();
                $comment->entry   = $id;
                $comment->created = NF_DateTime::now();
                $comment->ip      = $_SERVER['REMOTE_ADDR'];
                $comment->signed  = $name;
                $comment->email   = $email;
                $comment->text    = $text;

                // Insert the comment into the database. Since Persistence knows
                // the type of the object (Data_BlogComment), it also knows
                // which table to put it in.
                NF::persist()->insert($comment);

                // slowDirect generates a little special page with META redirect
                // and JavaScript redirect, instead of a 301 Redirection call.
                // This means that if the user hits F5 (refresh), the browser
                // doesn't try to make a POST call again, which would be the
                // inevitable result with just $Response->redirect('');
                //
                // Since we give no specification (''), the current page is
                // basically reloaded - but with a GET, which triggers the
                // view item code.
                NF::response()->slowRedirect('');
            }
            else
            {
                // Something failed. Just basically give the "view item" stuff
                // back with an error message.
                $entry = NF::persist()->load('Data_BlogEntry', $id);
                NF::persist()->loadRelated($entry, 'objComments');

                // _t('') means "translate". It depends on the browser's locale
                // and if we made any set-lang calls.
                NF::response()->content = NF_Template::runDefault(null, array(
                    'entry' => $entry,
                    'form'  => $this->iv->inject(),
                    'errmsg' => _t('One or more of the fields below were left blank!')
                ));
            }
        }
    }

    /**
     * Allows us to set the language. Called from the main.phtml code through a
     * jQuery AJAX call. Nifty automatically recognizes AJAX calls and handles
     * them differently internally - there is no master template applied, etc.
     * But all of this is completely transparent to us.
     *
     * Here we just set the locale given as the $id parameter. The main.phtml code
     * is resposible for reloading the page.
     *
     * @param string $id
     */
    public function executeSetLang($id)
    {
        NF::translate()->setLocale($id);
    }

    /**
     * Just a small test page that allows us to test various things quickly.
     */
    public function executeTest()
    {
        NF::response()->content = NF_Template::run('test.phtml');
    }
}
