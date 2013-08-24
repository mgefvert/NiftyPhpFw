<?php

class NF_CSV
{
    public $data          = array();
    public $fields        = array();
    public $titles        = array();
    public $formats       = array();
    public $includeHeader = true;
    public $alwaysEscape  = true;

    const Number = 1;
    const String = 2;
    const Date   = 3;

    protected function fixUpFields()
    {
        if (empty($this->fields))
        {
            $item = reset($this->data);
            $this->fields = is_object($item) ? array_keys(get_object_vars($item)) : array_keys($item);
        }

        foreach($this->fields as $k => $v)
        {
            if (!isset($this->titles[$k]))
                $this->titles[$k] = $v;
            if (!isset($this->formats[$k]))
                $this->formats[$k] = self::String;
        }
    }

    protected function renderHeader()
    {
        return '"' . implode('";"', $this->titles) . "\"\r\n";
    }

    protected function renderRow($row)
    {
        $result = array();

        foreach($this->fields as $index => $field)
        {
            $value = a($row, $field);
            switch($this->formats[$index])
            {
                case self::Number:
                    $result[] = str_replace('.', ',', $value);
                    break;

                case self::String:
                    if (substr($value, 0, 1) == '-' && !is_numeric($value))
                        $value = ' ' . $value;
                    if ($this->alwaysEscape || strpos($value, '"') !== false)
                        $result[] = '"' . str_replace('"', '\"', $value) . '"';
                    else
                        $result[] = $value;
                    break;

                case self::Date:
                    $x = new NF_DateTime($value);
                    $result[] = (string)$x;
                    break;

                default:
                    throw new Exception('Invalid format in CSV export');
            }
        }

        return implode(';', $result) . "\r\n";
    }

    public function render()
    {
        $this->fixUpFields();

        $output = '';

        if ($this->includeHeader)
            $output .= $this->renderHeader();

        foreach($this->data as $row)
            $output .= $this->renderRow($row);

        return $output;
    }
}
