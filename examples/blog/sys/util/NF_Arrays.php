<?php

/**
 * Array utility functions
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Arrays
{
    /**
     * Transforms an array of arrays into an array of objects
     *
     * @param array $list
     * @return type
     */
    public static function objectsFromArrays(array $list)
    {
        $result = array();
        foreach($list as $item)
            $result[] = (object)$item;

        return $result;
    }

    /**
     *
     * @param type $objects
     * @param type $field
     * @return type
     */
    public static function hash($objects, $field)
    {
        $result = array();
        foreach($objects as $obj)
            if (is_object($obj))
                $result[$obj->$field] = $obj;
            else
                $result[$obj[$field]] = $obj;

        return $result;
    }

    protected static function __group1($objects, $isObj, $f1)
    {
        $result = array();
        foreach($objects as $k => $o)
        {
            $v1 = $isObj ? $o->$f1 : $o[$f1];
            $result[$v1][$k] = $o;
        }

        return $result;
    }

    protected static function __group2($objects, $isObj, $f1, $f2)
    {
        $result = array();
        foreach($objects as $o)
        {
            $v1 = $isObj ? $o->$f1 : $o[$f1];
            $v2 = $isObj ? $o->$f2 : $o[$f2];
            $result[$v1][$v2][] = $o;
        }

        return $result;
    }

    protected static function __group3($objects, $isObj, $f1, $f2, $f3)
    {
        $result = array();
        foreach($objects as $o)
        {
            $v1 = $isObj ? $o->$f1 : $o[$f1];
            $v2 = $isObj ? $o->$f2 : $o[$f2];
            $v3 = $isObj ? $o->$f3 : $o[$f3];
            $result[$v1][$v2][$v3][] = $o;
        }

        return $result;
    }

    /**
     * Groups objects by field.
     *
     * @param array $objects
     * @param string $field
     * @param string $field2
     * @param string $field3
     * @return array
     */
    public static function group($objects, $field, $field2 = null, $field3 = null)
    {
        $level = 1;
        if ($field2)
        {
            $level++;
            if ($field3)
                $level++;
        }

        switch($level)
        {
            case 1: return self::__group1($objects, is_object(reset($objects)), $field);
            case 2: return self::__group2($objects, is_object(reset($objects)), $field, $field2);
            case 3: return self::__group3($objects, is_object(reset($objects)), $field, $field2, $field3);
        }
    }

    /**
     * Provides the logic for a(). Accesses a particular field, subfield or
     * member of array, opting for the default value if the field isn't found.
     * Takes sequences of fields, like "field1->field2->field3". Works on arrays.
     *
     * @param mixed $obj
     * @param string $key
     * @param mixed $default
     */
    public static function access($obj, $key, $default = null)
    {
        $key = explode('->', $key);
        if (empty($key))
            return $default;

        foreach($key as $s)
            if (is_object($obj) && property_exists($obj, $s))
                $obj = $obj->$s;
            else if (is_array($obj) && array_key_exists($s, $obj))
                $obj = $obj[$s];
            else
                $obj = $default;

        return $obj;
    }

    /**
     * Extracts a particular property from an array of objects. Returns
     * a single array with the property from all objects extracted.
     *
     * @param type $obj
     * @param type $key
     */
    public static function extract($obj, $key)
    {
        if (empty($obj))
            return array();

        $result = array();
        if (is_array(reset($obj)))
        {
            foreach($obj as $o)
                $result[] = $o[$key];
        }
        else
        {
            foreach($obj as $o)
                $result[] = $o->$key;
        }

        return $result;
    }

    public static function sql_and(array $conditions)
    {
        return empty($conditions) ? '' : '((' . implode(') and (', $conditions) . '))';
    }

    public static function sql_or(array $conditions)
    {
        return empty($conditions) ? '' : '((' . implode(') or (', $conditions) . '))';
    }
}
