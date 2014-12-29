<?php

/**
 * Class that encapsulates the request parameters, and also
 * invokes pages based on parameters
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Request extends NF_Elements
{
    private $_context;
    public $_path;
    public $_page;
    public $_cmd;
    public $id;
    public $handleExceptions = true;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hyphenToUnderscore = true;

        $utf8 = NF::config()->main->utf8;
        $ajax = $this->isAjax();
        $x = $_GET + $_POST;

        foreach($x as $k => $v)
        {
            if (get_magic_quotes_gpc())
                $v = stripslashes($v);

            if ($ajax && $utf8 == false)
                $v = NF_Text::fromUnicode($v);

            $this->$k = $v;
        }

        $this->_context = array();

        $this->_path = a($_SERVER, 'REDIRECT_URL', a($_SERVER, 'REQUEST_URI'));
        if (substr($this->path, 0, 1) != '/')
            $this->_path = '/' . $this->_path;

        if (($n = strpos($this->_path, '?')) !== false)
            $this->_path = substr($this->_path, 0, $n);

        if (strtolower(substr($this->_path, -5)) == '.html' || strtolower(substr($this->_path, -5)) == '.asmx')
            $this->_path = substr($this->_path, 0, -5);
    }

    public function elements()
    {
        $result = parent::elements();

        if ($this->_page !== null) $result['_page'] = $this->_page;
        if ($this->_cmd  !== null) $result['_cmd']  = $this->_cmd;
        if ($this->id    !== null) $result['id']    = $this->id;

        return $result;
    }

    /**
     * Wash an input string so it's suitable for processing as a request
     * item - class, object, variable etc. By default allows only
     * A-Z, a-z, 0-9, and hyphen (-) and underscore (_).
     *
     * @param string $text        Input string
     * @param string $additional  Additional allowed characters
     * @return string
     */
    public static function safeWash($text, $additional = '')
    {
        return preg_replace("/[^A-Za-z0-9\-_{$additional}]/", '', $text);
    }

    /**
     * Return a request variable
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->$key;
    }

    /**
     * Look through the parameters sent and return a list of elements.
     *
     * For instance, calling the page with a query string like
     *   person-1-name=Matt
     *   person-1-phone=5550001
     *   person-2-name=Joe
     *   person-2-phone=5550002
     *
     * can be captured using the call
     *   NF::request()->getList('person')
     *
     * and will return an array that looks like
     *   array(
     *     1 => array('name' => 'Matt', 'phone' => '5550001'),
     *     2 => array('name' => 'Joe',  'phone' => '5550002')
     *   )
     *
     * @param string $root Root to look for
     * @return array
     */
    public function getList($root)
    {
        $root .= '-';
        $rootlen = strlen($root);

        $result = array();
        foreach($this->_elem as $k => $v)
            if (substr($k, 0, $rootlen) == $root)
            {
                $k = explode('-', substr($k, $rootlen));

                if (count($k) == 1)
                    $result[$k[0]] = $v;
                else if (count($k) == 2)
                    $result[$k[0]][$k[1]] = $v;
                else if (count($k) == 3)
                    $result[$k[0]][$k[1]][$k[2]] = $v;
            }

        return $result;
    }

    /**
     *  Get a request parameter and force it to be numeric
     *
     *  @param string $key     Request parameter
     *  @param int    $default Optional default value
     *  @return int
     */
    public function getNumeric($key, $default=0)
    {
        $n = $this->$key;
        if ($n == '')
            $n = $default;
        if (!is_numeric($n))
            throw new NF_Exception("Value $key is not numeric!");

        return (int)$n;
    }

    /**
     *  Get a request parameter and mangle it to be safe. Only allowed characters
     *  are A-Z, a-z, 0-9 and "-".
     *
     *  @param string $key     Request parameter
     *  @param string $default Optional default value
     *  @return string
     */
    public function getSafe($key, $default = '')
    {
        $s = self::safeWash($this->$key);

        return $s ? $s : $default;
    }

    /**
     *  Examines the URL and executes a call to the correct instantiated
     *  class/function. Catches exceptions and forward to error pages
     *  automatically.
     *
     *  @return void
     */
    public function invoke()
    {
        try
        {
            NF::app()->init();

            if ($this->isAjax())
                NF::response()->reset();

            $routeClass = NF::route();
            $route = new $routeClass();
            $route->routeToPage($this->_path);

            if ($route->id !== null)
                $this->id = $route->id;
            $this->_page = $route->page;
            $this->_cmd  = $route->cmd;
            $this->_path = '/' . str_replace('_', '/', $this->_page) . '/' . $this->_cmd . ($this->id ? '/' . $this->id : '');

            $page = NF_PageBase::load($route->page);
            if ($page == null)
                throw new NF_EPageNotFound();

            $page->execute($route->cmd, $this->elements());

            NF::app()->finish($route);
        }
        catch(Exception $e)
        {
            if (!$this->handleExceptions)
                throw $e;

            $isNF = is_a($e, 'NF_Exception');
            $errCode = $isNF ? $e->httpErrorCode    : 500;
            $errText = $isNF ? $e->httpErrorMessage : 'Application fault';
            $direct  = $isNF ? $e->directOutput     : false;

            if (!$direct && ($page = NF_PageBase::load('error')) != null && NF::response()->direct == false)
                $page->execute($errCode, array('exception' => $e));
            else
            {
                if ($direct)
                    NF::response()->reset();

                header("{$_SERVER['SERVER_PROTOCOL']} $errCode $errText");
                NF::response()->content = method_exists($e, 'displayException')
                        ? $e->displayException()
                        : displayException($e);

                if (NF::response()->direct)
                    echo NF::response()->content;
            }
        }
    }

    /**
     *  Checks to see if the request is an Ajax request through the X-Requested-With header
     *
     *  @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    /**
     *  Checks to see if the request is a HTTP GET
     *
     *  @return bool
     */
    public function isGet()
    {
        return isset($_SERVER['REQUEST_METHOD']) &&
               $_SERVER['REQUEST_METHOD'] == 'GET';
    }

    /**
     *  Checks to see if the request is a HTTP POST
     *
     *  @return bool
     */
    public function isPost()
    {
        return isset($_SERVER['REQUEST_METHOD']) &&
               $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    /**
     * Push a context internally - used for components, so they can self-reference
     */
    public function pushContext($context, $method)
    {
        if (!is_a($context, 'NF_Component') && !is_a($context, 'NF_PageBase'))
            throw new NF_EAssertionFailed('Context is not NF_Component or NF_PageBase');

        array_push($this->_context, array('context' => $context, 'method' => $method));
    }

    /**
     * Return from a context
     */
    public function popContext()
    {
        return array_pop($this->_context);
    }

    /**
     * Get the active context - either a page or a component
     */
    public function getContext()
    {
        if (end($this->_context) === false)
            return null;

        $context = current($this->_context);
        return $context['context'];
    }

    /**
     * Get the context method, for self-referencing
     */
    public function getContextMethod()
    {
        if (end($this->_context) === false)
            return null;

        $context = current($this->_context);
        return $context['method'];
    }
}
