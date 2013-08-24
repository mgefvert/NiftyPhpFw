<?php

/**
 * Loads and parses application menus.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_MenuXML
{
    public $menu = array();
    protected $cache = array();

    public function __construct($menuFile = null)
    {
        if ($menuFile)
            $this->parseXmlFile($menuFile);
    }

    public function activeUrl($url)
    {
        $node = null;
        $currentArray = array_filter(explode('/', $url));
        while (!empty($currentArray))
        {
            $current = '/' . implode('/', $currentArray);
            if (array_key_exists($current, $this->cache))
            {
                $node = $this->cache[$current];
                break;
            }
            array_pop($currentArray);
        }

        if ($node == null)
            return;

        $node->active = $node->current = true;
        for(; $node != null; $node = $node->parent)
            if ($node)
                $node->active = true;
    }

    public static function build($menuFile, array $disabledUrls = null)
    {
        $menu = new NF_MenuXML($menuFile);

        if (!empty($disabledUrls))
            $menu->disableUrls($disabledUrls);
        $menu->activeUrl(NF::request()->_path);

        return $menu;
    }

    protected function buildCache(array $items)
    {
        foreach($items as $item)
        {
            $this->cache[$item->href] = $item;
            if (!empty($item->items))
                $this->buildCache($item->items);
        }
    }

    protected function disableAll(array &$menu)
    {
        foreach($menu as $item)
        {
            $item->enabled = false;
            if (!empty($item->items))
                $this->disableAll($item->items);
        }
    }

    public function disableUrls(array $urls)
    {
        foreach($urls as $url)
        {
            $url = $this->processUrl($url);
            if (array_key_exists($url, $this->cache))
            {
                $this->cache[$url]->enabled = false;
                if (!empty($this->cache[$url]->items))
                    $this->disableAll($this->cache[$url]->items);
            }
        }
    }

    public function get()
    {
        return $this->menu;
    }

    public function getSubmenu()
    {
        foreach($this->menu as $item)
            if ($item->active)
                return $item->items;

        return array();
    }

    protected function parseNode(SimpleXMLElement $node, $parent)
    {
        $result = array();
        foreach($node as $nodeItem)
        {
            $attr = $nodeItem->attributes();
            $menuItem = (object)array(
                'title'   => (string)$attr['title'],
                'href'    => $this->processUrl((string)$attr['href']),
                'enabled' => true,
                'active'  => false,
                'current' => false,
                'parent'  => $parent,
            );
            $menuItem->items = $nodeItem->count() ? $this->parseNode($nodeItem, $menuItem) : null;
            $result[] = $menuItem;
        }

        return $result;
    }

    public function parseXmlFile($menuFile)
    {
        $cacheid = '_menu_' . NF_Filter::name($menuFile);

        if (dirname($menuFile) == '.')
            $menuFile = NF_Path::$app . $menuFile;

        if (!file_exists($menuFile))
            throw new NF_Exception("Menu file not found.");

        $this->menu = NF::cache()->get($cacheid, filemtime($menuFile));
        if (empty($this->menu))
        {
            $this->menu = $this->parseNode(simplexml_load_file($menuFile), null);
            NF::cache()->set($cacheid, $this->menu);
        }

        $this->buildCache($this->menu);
    }

    protected function processUrl($url)
    {
        return '/' . implode('/', array_filter(explode('/', $url)));
    }

    public static function render(array $items, $class = null)
    {
        $result = '<ul' . ($class ? " class='$class'" : '') . '>';

        $lastActive = false;
        foreach($items as $item)
        {
            $class = array();
            if ($item->active) $class[] = 'active';
            if ($item->current) $class[] = 'current';
            $class = implode(' ', $class);

            $result .= "<li class='$class'><a href='$item->href'>" . html($item->title) . "<span></span></a>";
            if ($item->active && !empty($item->items))
                $result .= self::render($item->items);
            $result .= "</li>";

            $lastActive = $item->active;
        }

        $result .= '</ul>';

        return $result;
    }
}
