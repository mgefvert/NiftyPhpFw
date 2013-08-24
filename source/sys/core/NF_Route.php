<?php

/**
 * Defines the default routing mechanism
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Route
{
    public $path;
    public $page;
    public $cmd;
    public $id;

    protected function huntPage(array &$pathArray)
    {
        // Determine correct page by step-by-step cutting off up to two elements
        // (method and id) and see if we can reach a particular page.
        for ($i=count($pathArray); $i >= count($pathArray)-2 && $i > 0; $i--)
        {
            $p = NF_Request::safeWash(implode('_', array_slice($pathArray, 0, $i)), '\/');
            if (NF_PageBase::pageExists($p))
                return NF_Request::safeWash(implode('_', array_splice($pathArray, 0, $i)), '\/');
        }

        return false;
    }

    protected function applyCommonRoutes()
    {
        $routes = NF::config()->common_routes->elements();

        while (isset($routes[$this->path]))
        {
            $newpath = $routes[$this->path];
            unset($routes[$this->path]);
            $this->path = $newpath;
        }
    }

    public static function fixPath($path)
    {
        // Normalize double slashes into single slashes
        while(strpos($path, '//') !== false)
            $path = str_replace('//', '/', $path);

        // Make sure it begins with a slash
        if (substr($path, 0, 1) != '/')
            $path = '/' . $path;

        // Make sure it doesn't end with a slash
        if (strlen($path) > 1 && substr($path, -1) == '/')
            $path = substr($path, 0, -1);

        // Trim away the site part, if any
        $site = NF_Path::excludeTrailingSlash(NF_Path::$siteURL);
        if (strcasecmp($site, substr($path, 0, strlen($site))) == 0)
            $path = substr($path, strlen($site));

        if (strlen($path) == 0)
            $path = '/';

        return $path;
    }

    public function routeToPage($path)
    {
        $this->page = null;
        $this->cmd  = null;
        $this->id   = null;

        $this->path = $this->fixPath($path);

        NF::app()->preRoute($this);
        $this->applyCommonRoutes();

        $pathArray = array_filter(explode('/', $this->path), function($p) { return strlen($p) > 0; });

        if (empty($pathArray))
            $this->page = 'index';
        else
        {
            // See if we can match the page
            if (($p = $this->huntPage($pathArray)) === false)
            {
                // No page found, try tacking on /index and see if that helps
                $path2 = $pathArray;
                array_push($path2, 'index');
                if (($p = $this->huntPage($path2)) === false)
                {
                    // Didn't work either. Quit.
                    return;
                }

                $pathArray = $path2;
            }

            $this->page = $p;
        }

        $this->cmd = empty($pathArray) ? 'view' : NF_Request::safeWash(array_shift($pathArray));
        $this->id  = empty($pathArray) ? null : rawurldecode(array_shift($pathArray));

        NF::app()->postRoute($this);
    }
}
