<?php

/**
 * Show a dialog with the website settings.
 */
class Admin_Settings extends AdminPage
{
    public function executeView()
    {
        global $Response, $Request;

        $formHelper = new NF_FormHelper();

        if ($Request->isGet())
        {
            // Don't really need to load the settings ... they're already
            // loaded by $Application.
            $Response->content = NF_Template::runDefault(null, array(
                'form' => $formHelper->inject()
            ));
        }
        else if ($Request->isPost())
        {
            // Save the settings.
            Data_BlogSetting::saveSetting('title', trim($Request->title));
            Data_BlogSetting::saveSetting('url', trim($Request->url));

            // Since the settings are cached by $Application in the site cache,
            // we have to clear the cache to make them visible.
            NF_Cache::clear();

            // Redirect "slowly" (not a 301 status) to the main page again.
            $Response->slowRedirect('/admin/main');
        }
    }
}
