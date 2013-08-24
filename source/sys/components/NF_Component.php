<?php

/**
 *  Base class for form components
 *
 *  PHP Version 5.3
 *
 *  @package    NiftyFramework
 *  @author     Mats Gefvert <mats@gefvert.se>
 *  @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
abstract class NF_Component extends NF_Elements implements NF_IContext
{
    public $id   = null;
    public $wrap = true;
    protected $_page = null;

    // --- Magic methods ---

    public function __construct(array $attributes = null)
    {
        if (!empty($attributes))
            foreach($attributes as $k => $v)
                $this->$k = $v;
    }

    public function __toString()
    {
        return $this->render();
    }

    // --- Important functions ---

    public function init()
    {
    }

    public function setup()
    {
    }

    public function postback()
    {
    }

    public function finalize()
    {
    }

    protected function beforeRender()
    {
    }

    protected abstract function renderItem();

    // --- Main render function ---

    public function render()
    {
        $id = html($this->id);
        NF::request()->pushContext($this, 'view');
        try
        {
            $this->beforeRender();
            $result = $this->renderItem();
            if ($this->wrap)
                $result = "<div id='$id' class='component'>$result</div>";
        }
        catch(Exception $err)
        {
            $result = "<div id='$id' class='component error'>" . html($err->getMessage()) . "</div>";
        }
        NF::request()->popContext();
        return $result;
    }

    // --- Public support functions ---

    public function getPage()
    {
        return $this->_page;
    }

    public function setPage(NF_PageBase $page)
    {
        $this->_page = $page;
    }

    public function ref($method = null, $id = null, array $params = null)
    {
        if (!$this->_page)
            throw new NF_EAssertionFailed("Can't self-reference component when not attached to a page");

        $result = $this->_page->ref('') . 'callback?c=' . rawurlencode($this->id);
        if ($method !== null)
            $result .= '&m=' . rawurlencode($method);

        if ($id !== null)
            $result .= '&id=' . rawurlencode($id);

        if ($params)
        {
            foreach($params as $k => &$ref_v)
                $ref_v = rawurlencode($k) . '=' . rawurlencode($ref_v);
            $result .= '&' . implode('&', array_values($params));
        }

        return $result;
    }

    public function refPath($file = null)
    {
        $component = str_replace('_', '/', get_class($this));
        return "components/$component/$file";
    }

    public static function tryLoad($componentName, $parameters = null)
    {
        $componentName = NF_Filter::name($componentName);
        $root = NF_Path::$components;

        if (!NF_Autoloader::isNiftyClass($componentName))
            NF_Autoloader::loadClass($root, $componentName);

        if (!class_exists($componentName))
            return null;

        if (!is_subclass_of($componentName, 'NF_Component'))
            return null;

        $component = new $componentName();

        if (!empty($parameters))
            foreach ($parameters as $k => $v)
                $component->$k = $v;

        return $component;
    }

    /**
     * @return NF_Component
     */
    public static function load($className, $parameters = null)
    {
        if (($component = self::tryLoad($className, $parameters)) == null)
            throw new Exception("Cannot instantiate component $className");

        return $component;
    }

    /**
     *  Load and run a component directly, bypassing all normal handling
     *
     *  @return string Returns the rendered HTML.
     */
    public static function run($className, $parameters = null)
    {
        $component = self::load($className, $parameters);

        $component->init();
        $component->setup();
        $html = $component->render();
        $component->finalize();

        return $html;
    }
}
