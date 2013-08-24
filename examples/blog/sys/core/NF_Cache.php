<?php

/**
 * The cache objects provides an interface to caching and the /app/cache folder
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Cache
{
    public $ageSeconds = 3600;  // Default age an hour

    /**
     *  Checks to see if a particular cached file has expired. Files
     *  named with __* never expire, but are system data.
     *
     *  @param string $filename Filename in the /app/cache folder
     *  @return bool True if expired, false if OK
     */
    private function old($filename, $timestamp)
    {
        if (substr(basename($filename), 0, 2) != '__')
        {
            if ($timestamp == null)
                $timestamp = time() - $this->ageSeconds;

            return filemtime($filename) < $timestamp;
        }
        else
            return false;
    }

    /**
     *  Set the cache item ID to a particular data string and writes it
     *  to the cache.
     *
     *  @param string $id   Cache identifier
     *  @param string $data Data to write
     */
    public function set($id, $data)
    {
        $id = NF_Filter::name($id);
        if ($id == '')
            return;

        file_put_contents(NF_Path::$cache . $id, serialize($data));
    }

    /**
     *  Check to see if a particular item ID exists in the cache and verifies
     *  that it is valid
     *
     *  @param string $id        Cache identifier
     *  @param int    $timestamp Timestamp value to compare against, or NULL to use
     *                           the class default value.
     *  @return bool
     */
    public function exists($id, $timestamp = null)
    {
        $id = NF_Filter::name($id);
        if ($id == '')
            return;

        $fn = NF_Path::$cache . $id;

        return file_exists($fn) && (!isset($this) || !$this->old($fn, $timestamp));
    }

    /**
     *  Get an item ID from the cache. If it doesn't exist or is expired, returns
     *  False. Can be invoked statically, if need be (such as during bootstrapping).
     *
     *  @param string $id        Cache identifier
     *  @param int    $timestamp Timestamp value to compare against, or NULL to use
     *                           the class default value.
     *  @return mixed False if not found, otherwise the data string
     */
    public function get($id, $timestamp = null)
    {
        $id = NF_Filter::name($id);
        if (!$id)
            return;

        $fn = NF_Path::$cache . $id;
        if (!file_exists($fn) || (isset($this) && $this->old($fn, $timestamp)))
            return false;

        return unserialize(file_get_contents($fn));
    }

    /**
     *  Clears the entire cache
     */
    public function clear()
    {
        $files = glob(NF_Path::$cache . '*');
        if (empty($files))
            return;

        foreach($files as $file)
            if (substr(basename($file), 0, 2) != '__')
                unlink($file);
    }

    /**
     *  Process the cache and expire old entries
     */
    public function process()
    {
        $files = glob(NF_Path::$cache . '*');
        if (empty($files))
            return;

        foreach($files as $file)
            if ($this->old($file, $this->ageSeconds))
                unlink($file);
    }
}
