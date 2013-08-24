<?php

/**
 * The Application class is always loaded by the system - if it exists - and
 * is made available in the global $Application variable.
 *
 * What we do here is simply just to load the settings for the blog, such as
 * the blog title and URL. It is also saved in the cache for speedy access.
 */
class Application
{
    public $appSettings = array();
    public $appTitle;
    public $appURL;

    public function init()
    {
        $value = NF_Cache::get('appsettings');

        if ($value)
            $this->appSettings = unserialize($value);
        else
        {
            $this->appSettings = Data_BlogSetting::loadSettingsArray();
            NF_Cache::set('appsettings', serialize($this->appSettings));
        }

        $this->appTitle = a($this->appSettings, 'title', 'A Blog');
        $this->appURL = a($this->appSettings, 'url');
    }

    public function finish()
    {
        NF_Cache::process();
    }
}
