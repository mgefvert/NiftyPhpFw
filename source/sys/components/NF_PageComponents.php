<?php

/**
 *  Component instantiator; automatically embedded in Pages, for instance
 *
 *  PHP Version 5.3
 *
 *  @package    NiftyFramework
 *  @author     Mats Gefvert <mats@gefvert.se>
 *  @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_PageComponents
{
    protected $_components = array();
    protected $_page;

    public function __construct(NF_PageBase $page)
    {
        $this->_page = $page;
    }

    public function clear()
    {
        $this->_components = array();
    }

    public function init()
    {
        foreach($this->_components as $component)
            $component->init();
    }

    public function setup()
    {
        foreach($this->_components as $component)
            $component->setup();
    }

    public function finalize()
    {
        foreach($this->_components as $component)
            $component->finalize();
    }

    public function add($id, NF_Component $component)
    {
        if (isset($this->_components[$id]))
            throw new Exception("Component '$id' already exists in page module array.");

        $component->setPage($this->_page);
        $component->id = $id;

        $this->_components[$id] = $component;

        return $component;
    }

    public function create($id, $componentName, array $parameters = null)
    {
        $component = NF_Component::load($componentName, $parameters);
        $this->add($id, $component);

        return $component;
    }

    public function exists($componentName)
    {
        return isset($this->_components[$componentName]);
    }

    public function getComponentList()
    {
        return array_keys($this->_components);
    }

    public function remove($id)
    {
        unset($this->_components[$id]);
    }

    public function __get($item)
    {
        if (isset($this->_components[$item]))
            return $this->_components[$item];
        else
            throw new Exception("Can't find component '$item' in page.");
    }
}
