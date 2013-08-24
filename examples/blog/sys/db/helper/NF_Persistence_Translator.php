<?php

/**
 * Helper class that translates properties into fields
 */
class NF_Persistence_Translator
{
    private $classname;
    private $db;
    private $table;
    private $idfield;
    private $fieldmap;

    public function __construct($classname, NF_IDatabase $database)
    {
        NF_Persistence_Map::getMap($classname, $this->table, $this->idfield, $this->fieldmap);

        $this->classname = $classname;
        $this->db        = $database;
    }

    public function translateField($field)
    {
        $fields = explode(':', $field);

        if (count($fields) == 1)
            return $this->db->quoteField(isset($this->fieldmap[$field]) ? $this->fieldmap[$field] : $field);
        else if (count($fields == 2))
        {
            list($field, $class) = $fields;
            NF_Persistence_Map::getMap($class, $table, $idfield, $fieldmap);

            if (!$field)
                return $this->db->quoteField($table);
            else
                return $this->db->quoteField(isset($fieldmap[$field])
                    ? $table . '.' . $fieldmap[$field]
                    : $table . '.' . $field);
        }
        else
            throw new NF_EDatabaseError('Invalid field definition: ' . $field);
    }

    public function translate($query)
    {
        $self = $this;

        $query = preg_replace_callback(
            '|\[([\w\d:]+)\]|',
            function($matches) use($self) { return $self->translateField($matches[1]); },
            $query
        );

        return $query;
    }
}
