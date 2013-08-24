<?php

NF_Persistence::mapTable('Data_BlogSetting', 'blog_settings', 'id');
NF_Persistence::mapFields('Data_BlogSetting', array(
    'key'   => 'bs_key',
    'value' => 'bs_value',
));

/**
 * Class that contains blog settings.
 */
class Data_BlogSetting
{
    public $key;
    public $value;

    /**
     * Load all settings as a lookup array, with all the settings available
     * as key => value in the array.
     *
     * @global NF_Persistence $Persistence
     * @return array
     */
    public static function loadSettingsArray()
    {
        global $Persistence;

        return $Persistence->queryLookup(__CLASS__, 'select [key], [value] from [:Data_BlogSetting]');
    }

    /**
     * Save a particular setting. Creates a Data_BlogSetting object which it
     * replaces into the table, using the MySQL REPLACE INTO command.
     *
     * @global NF_Persistence $Persistence
     * @param string $key
     * @param string $value
     */
    public static function saveSetting($key, $value)
    {
        global $Persistence;

        $obj = new Data_BlogSetting();
        $obj->key = $key;
        $obj->value = $value;

        $Persistence->replace($obj);
    }
}
