<?php

/**
 * Base class for all Page objects. All pages must eventually inherit from this.
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_PageBase implements NF_IContext
{
    public $sessionObject = null;
    private $_init = false;      // Set if init() called, otherwise we failed
    private $_finalize = false;  // These are to prevent security failures with AuthPage

    /**
     * @var NF_PageComponents
     */
    public $Components = null;

    /**
     *  Public constructor for NF_PageBase.
     */
    public function __construct()
    {
        $this->Components = new NF_PageComponents($this);
    }

    /**
     *  Overridable initialization method, default is to do nothing
     */
    protected function init()
    {
        $this->_init = true;
    }

    /**
     *  doInit initializes a particular page
     */
    private function _doInit()
    {
        if (isset($_SESSION['pageStore'][get_class($this)]))
            $this->sessionObject =
                unserialize($_SESSION['pageStore'][get_class($this)]);

        $this->init();

        if ($this->_init == false)
            throw new NF_Exception('NF_PageBase::init() was not called; check your parent::init calls.');
    }

    /**
     *  Overridable finalization method, default is to do nothing
     *
     *  @return void
     */
    protected function finalize()
    {
        $this->_finalize = true;
    }

    /**
     *  Clean up after a page, store away session data, etc.
     */
    private function _doFinalize()
    {
        global $_SESSION;

        $this->finalize();

        if (isset($this->sessionObject))
            $_SESSION['pageStore'][get_class($this)] = serialize($this->sessionObject);
        else
            unset($_SESSION['pageStore'][get_class($this)]);

        if ($this->_finalize == false)
            throw new NF_Exception('NF_PageBase::finalize() was not called; check your parent::finalize calls.');
    }

    /**
     *  PascalCase a particular name
     *
     *  @param string $cmd Command to PascalCase
     *
     *  @static
     *  @return string
     */
    public static function pascalCase($cmd)
    {
        return str_replace(' ', '', ucwords(strtolower(str_replace('-', ' ', $cmd))));
    }

    /**
     *  Revert a PascalCased method to a web-name (separated by hyphens)
     *
     *  @param string $method
     *  @return string
     */
    public static function inversePascalCase($method)
    {
        $result = '';

        for($i=0; $i<strlen($method); $i++)
        {
            $c = $method[$i];
            $result .= ctype_upper($c) ? '-' . strtolower($c) : $c;
        }

        if (strlen($result) > 0 && $result[0] == '-')
            $result = substr($result, 1);

        return $result;
    }

    /**
     *  Make a "sterilized" name for a page from a title, all strange letters
     *  converted to their ASCII counterparts, all spaces replaced by a single
     *  dash, etc.
     *
     *  @param string $title Input title
     *  @return string
     */
    public static function pageName($title)
    {
        $title = trim(strtolower(strip_tags(htmlspecialchars_decode($title, ENT_QUOTES))));

        $title = str_replace(' ', '-', $title);
        $title = preg_replace('/[^\w-]/', '', $title);
        $title = iconv('ISO-8859-1', 'ASCII//TRANSLIT', $title);
        $title = preg_replace('/([^a-z_\-0-9])/', '', $title);

        while (strpos($title, '--') !== false)
            $title = str_replace('--', '-', $title);

        return $title;
    }

    /**
     *  Return a list of the different "web methods" exposed by this class
     *
     *  @return array
     */
    public function getWebMethods()
    {
        $className = get_class($this);
        $methods   = get_class_methods($className);
        $webClass  = strtolower($className);

        $result = array();
        foreach($methods as $method)
            if (substr($method, 0, 7) == 'execute')
            {
                $r = new ReflectionMethod($className, $method);

                $webMethod = self::inversePascalCase(substr($method, 7));
                if ($webMethod != '')
                {
                    $params = $r->getParameters();
                    $webRequiredParams = array();
                    $webOptionalParams = array();
                    foreach($params as $param)
                    {
                        if ($param->isOptional())
                            $webOptionalParams[] = $param->getName();
                        else
                            $webRequiredParams[] = $param->getName();
                    }

                    $result[$webMethod] = array(
                        'url'        => "/$webClass/$webMethod",
                        'params'     => $webRequiredParams,
                        'opt_params' => $webOptionalParams
                    );
                }
            }

        return $result;
    }

    protected function _processCallback($elements)
    {
        NF::response()->reset();

        $c = isset($elements['c'])
            ? NF_Request::safeWash($elements['c'])
            : '';
        $m = 'execute' . (isset($elements['m'])
            ? self::pascalCase(NF_Request::safeWash($elements['m']))
            : '');

        if ($c == '')
            throw new NF_EInvalidRequest('Invalid component callback');

        $component = $this->Components->$c;

        if (!method_exists($component, $m))
            throw new NF_EPageNotFound('Component callback method not found');

        NF::request()->pushContext($component, NF_Request::safeWash($elements['m']));
        try
        {
            $component->init();
            if (NF::request()->isPost())
                $component->postback();
            else
                $component->setup();
            NF_invoke($component, $m, $elements);
            $component->finalize();
        }
        catch(Exception $err)
        {
            // I love the way the PHP devs didn't build support for "finally".
            NF::request()->popContext();
            throw $err;
        }
        NF::request()->popContext();
    }

    /**
     *  The execute method is the vital one that executes a specific call.
     *  It takes the "$cmd" parameter, translates it into a public "executeNnn"
     *  function (using executeView if no cmd was supplied), and calls it.
     *  Also passes along additional arguments on the URL and checks these
     *  against parameters in the executeNnn method, inserting these as required.
     *
     *  The execute method will also call the init() and finalize() methods.
     *
     *  @param string $cmd      Optional command to execute
     *  @param array  $elements Optional elements
     */
    public function execute($cmd, $elements)
    {
        $this->_doInit();
        NF::request()->pushContext($this, $cmd);
        try
        {
            if ($cmd == 'callback')
                $this->_processCallback($elements);
            else
            {
                $method = 'execute' . self::pascalCase($cmd);

                if (!method_exists($this, $method))
                {
                    $method = 'executeDefault';
                    if (!method_exists($this, $method))
                        throw new NF_EPageNotFound();
                }

                $this->Components->init();
                if (!NF::request()->isPost())
                    $this->Components->setup();

                NF::app()->preInvoke($this, $cmd, $elements);
                NF_invoke($this, $method, $elements);
                NF::app()->postInvoke($this, $cmd, $elements);

                $this->Components->finalize();
            }
        }
        catch(Exception $err)
        {
            NF::request()->popContext();
            $this->_doFinalize();
            throw $err;
        }
        NF::request()->popContext();
        $this->_doFinalize();
    }

    /**
     *  Quick function to see if a page exists or not
     *
     *  @param string $page Page class to load
     *  @return bool
     */
    public static function pageExists($page)
    {
        return NF_Autoloader::classExists(NF_Path::$pages, NF_Filter::name(self::pascalCase($page))
        );
    }

    /**
     *  Function to load a page
     *
     *  @param string $page Page class to load
     *  @return NF_PageBase
     */
    public static function load($page)
    {
        $page = NF_Filter::name(self::pascalCase($page));

        if (!class_exists($page, false))
        {
            $root = NF_Path::$pages;

            if (NF_Autoloader::classExists($root, $page))
                NF_Autoloader::loadClass($root, $page);

            if (!class_exists($page, false))
                return null;
        }

        if (!is_subclass_of($page, 'NF_PageBase'))
            throw new Exception("Page $page is not a NF_PageBase class");

        return new $page();
    }

    public static function classToUrl($class)
    {
        $words = explode('_', $class);
        foreach($words as &$ref_word)
        {
            $ref_word[0] = strtolower($ref_word[0]);
            $ref_word = self::inversePascalCase($ref_word);
        }

        return implode('/', $words);
    }

    public function ref($method = null, $id = null, array $params = null)
    {
        $page = self::classToUrl(get_class($this));
        $ref = $method === null
            ? NF_Path::$siteURL . "$page/" . rawurlencode(NF::request()->_cmd)
            : NF_Path::$siteURL . "$page/$method";

        if ($id !== null)
            $ref .= '/' . rawurlencode($id);

        if ($params)
        {
            $ref .= '?';
            foreach($params as $k => &$ref_v)
                $ref_v = rawurlencode($k) . '=' . rawurlencode($ref_v);
            $ref .= implode('&', array_values($params));
        }

        return $ref;
    }

    public function refPath($file = null)
    {
        $words = explode('_', get_class($this));
        foreach($words as &$ref_word)
        {
            $ref_word[0] = strtolower($ref_word[0]);
            $ref_word = $this->inversePascalCase($ref_word);
        }

        $page = implode('/', $words);
        return "$page/$file";
    }
}
