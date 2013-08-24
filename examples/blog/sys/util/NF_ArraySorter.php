<?php

/**
 *  NF_ArraySorter can help with sorting arrays or arrays of objects.
 *
 *  PHP Version 5.3
 *
 *  @package    NiftyFramework
 *  @author     Mats Gefvert <mats@gefvert.se>
 *  @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_ArraySorter
{
    protected $fields = array();

    public function __construct(array $fields)
    {
        foreach($fields as $field)
            $this->fields[] = $this->_parseFieldParam($field);
    }

    private function _parseFieldParam($field)
    {
        $result = array();

        if (empty($field))
            return null;

        if ($field[0] == '-')
        {
            $field = substr($field, 1);
            $result['order'] = -1;
        }
        else
            $result['order'] = 1;

        $result['fields'] = explode('->', $field);

        return $result;
    }

    protected function sortItem($a, $b, $field)
    {
        $order = $field['order'];
        $fields = $field['fields'];

        foreach($fields as $f)
        {
            if (!$a)
                $a = null;
            else if (is_object($a))
                $a = $a->$f;
            else if (is_array($a))
                $a = $a[$f];

            if (!$b)
                $b = null;
            else if (is_object($b))
                $b = $b->$f;
            else if (is_array($b))
                $b = $b[$f];
        }

        if (is_numeric($a) && is_numeric($b))
        {
            if ($a > $b)
                return 1 * $order;
            else if ($a < $b)
                return -1 * $order;
            else
                return 0;
        }
        else
            return strcasecmp($a, $b) * $order;
    }

    public function compare($a, $b)
    {
        $res = 0;

        if (empty($this->fields))
            throw new Exception('NF_ArraySorter field definition list is empty');

        foreach($this->fields as $field)
            if (($res = $this->sortItem($a, $b, $field)) != 0)
                return $res;

        return $res;
    }

    public static function uasort(array &$data, $fields)
    {
        if (!is_array($fields))
            $fields = array($fields);

        $sorter = new NF_ArraySorter($fields);
        uasort($data, array($sorter, 'compare'));
    }

    public static function usort(array &$data, $fields)
    {
        if (!is_array($fields))
            $fields = array($fields);

        $sorter = new NF_ArraySorter($fields);
        usort($data, array($sorter, 'compare'));
    }
}
