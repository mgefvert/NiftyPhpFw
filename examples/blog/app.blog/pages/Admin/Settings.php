<?php

/**
 * Show a dialog with the website settings.
 */
class Admin_Settings extends AdminPage
{
    public function executeView()
    {
        $iv = new NF_IV();

        if (NF::request()->isGet())
        {
            // Don't really need to load the settings ... they're already
            // loaded by $Application.
            NF::response()->content = NF_Template::runDefault(null, array(
                'form' => $iv->inject()
            ));
        }
        else if (NF::request()->isPost())
        {
            // Save the settings.
            Data_BlogSetting::saveSetting('title', trim(NF::request()->title));
            Data_BlogSetting::saveSetting('url', trim(NF::request()->url));

            // Since the settings are cached by $Application in the site cache,
            // we have to clear the cache to make them visible.
            NF::cache()->clear();

            // Redirect "slowly" (not a 301 status) to the main page again.
            NF::response()->slowRedirect('/blog/admin/main');
        }
    }
}
