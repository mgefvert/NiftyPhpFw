<?php

/**
 * Templates are HTML/php scripts that gets filled and expanded with data upon
 * request.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Template extends NF_Elements
{
    private $_filename;
    private $_root;
    public $minify = false;

    /**
     *  Constructor for templates.
     *
     *  @param string $filename Filename of the template. Should be .phtml and is
     *                          always relative to /app/templates.
     *  @param string $root     If you want to override the /app/templates, specify
     *                          the full, new root here (like, /home/user00/www-root/public/skins)
     */
    public function __construct($filename, $root = '')
    {
        $this->_root = $root
            ? NF_Path::includeTrailingSlash($root)
            : NF_Path::$templates;

        $this->_filename     = $filename;
        $this->caseSensitive = false;
        $this->minify        = NF::config()->main->minify;

        parent::clear();
    }

    /**
     *  Clear the filled-in variable array
     *
     *  @return void
     */
    public function clear()
    {
        parent::clear();
    }

    public function ref($method = null, $id = null, array $params = null)
    {
        return NF::request()->getContext()->ref($method, $id, $params);
    }

    public function page()
    {
        return NF::request()->getContext();
    }

    public function getComponents()
    {
        $caller = $this->page();
        if ($caller && isset($caller->Components))
            return $caller->Components;
        else
            return null;
    }

    /**
     *  Parse the template, given the filled-in values, and return the result
     *
     *  @return string
     */
    public function parse()
    {
        if (!file_exists($this->_root . $this->_filename))
            throw new Exception("Template {$this->_filename} does not exist");

        $this->Components = $this->getComponents();

        ob_start();
        include $this->_root . $this->_filename;
        $result = ob_get_clean();

        $f = $this;
        $result = preg_replace_callback('|<@([A-Za-z0-9_]+)>|',    function($matches) use ($f) { return $f->{$matches[1]}; }, $result);
        $result = preg_replace_callback('|\[@(.+)\]|Us',           function($matches) { return _t($matches[1]); },      $result);

        return $this->minify ? NF_Text::minify($result) : $result;
    }

    protected static function getDefaultFilename($filename = '')
    {
        if (($context = NF::request()->getContext()) == null)
            throw new Exception('Can\'t load default template when not in context');

        if ($filename == '')
            $filename = NF::request()->getContextMethod() . '.phtml';

        return $context->refPath($filename);
    }

    public static function isfile($filename)
    {
        return file_exists(NF_Path::$templates . $filename);
    }

    public static function isfileDefault($filename = '')
    {
        return static::isfile(static::getDefaultFilename($filename));
    }

    /**
     *  Load a template, optionally filling in parameter values
     *
     *  @param string $filename   Name of template (.phtml)
     *  @param array  $parameters Optional parameter array
     *
     *  @static
     *  @return NF_Template
     */
    public static function load($filename, array $parameters = null)
    {
        $tmpl = new NF_Template($filename);

        if (($components = $tmpl->getComponents()) != null)
            foreach($components->getComponentList() as $id)
                $tmpl->$id = $components->$id;

        if (!is_null($parameters))
            foreach($parameters as $k => $v)
                $tmpl->$k = $v;

        return $tmpl;
    }

    public static function loadDefault($filename = null, array $parameters = null)
    {
        return static::load(static::getDefaultFilename($filename), $parameters);
    }

    /**
     *  Run a template, optionally filling in parameter values
     *
     *  @param string $filename   Name of template (.phtml)
     *  @param array  $parameters Optional parameter array
     *
     *  @static
     *  @return string
     */
    public static function run($filename, array $parameters = null)
    {
        $tmpl = static::load($filename, $parameters);
        return $tmpl->parse();
    }

    public static function runDefault($filename = null, array $parameters = null)
    {
        $tmpl = static::loadDefault($filename, $parameters);
        return $tmpl->parse();
    }
}
