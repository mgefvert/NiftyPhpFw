<?php

/**
 * Provides methods for dynamic properties. Inherited functions may use these
 * Elements behavior to initialize variables, etc. All of these are located in
 * the array $elem.
 *
 * PHP Version 5.3
 *
 * @package  NiftyFramework
 * @author   Mats Gefvert <mats@gefvert.se>
 * @license  http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Elements
{
    protected $_elem = array();

    /**
     *  Determine whether the object elements array is case-sensitive or not. Not
     *  accessible to outsiders; this should be set by the overriden class
     *  constructor.
     */
    protected $caseSensitive = true;

    /**
     *  Automatically translate all hyphens (-) into underscores (_). Makes it
     *  easier to look up values..
     */
    protected $hyphenToUnderscore = false;

    private function processName($name)
    {
        if ($this->caseSensitive == false)
            $name = strtolower($name);

        if ($this->hyphenToUnderscore)
            $name = str_replace('-', '_', $name);

        return $name;
    }

    /**
     *  Clear the array.
     */
    public function clear()
    {
        $this->_elem = array();
    }

    /**
     *  Get the elements array.
     *
     *  @return array
     */
    public function elements()
    {
        return $this->_elem;
    }

    /**
     *  Dynamic getter. Returns a blank string if the property isn't found.
     *
     *  @param string $name Name of property
     *  @return mixed
     */
    public function __get($name)
    {
        $name = $this->processName($name);
        return isset($this->_elem[$name]) ? $this->_elem[$name] : null;
    }

    /**
     *  Dynamic setter. If case-insensitive, all keys are first made lowercase.
     *
     *  @param string $name Name of property
     *  @param string $value     Value to set
     */
    public function __set($name, $value)
    {
        $name = $this->processName($name);
        $this->_elem[$name] = $value;
    }

    /**
     * Dynamic isset() function. Checks for the existance of a property named $name
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name)
    {
        $name = $this->processName($name);
        return isset($this->_elem[$name]);
    }

    /**
     * Removes a dynamic property.
     *
     * @param string $name
     */
    public function __unset($name)
    {
        $name = $this->processName($name);
        unset($this->_elem[$name]);
    }

    /**
     *  Does a certain key exist in the elements array?
     *
     *  @param string $key Key name
     *  @return bool True if the key exists.
     */
    public function exists($key)
    {
        $name = $this->processName($name);
        return isset($this->_elem[$key]);
    }
}
