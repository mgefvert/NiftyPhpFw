<?php

/**
 * Helper class for writing SQL queries
 */
class NF_Persistence_SQL
{
    protected $database;

    public function __construct(NF_IDatabase $database)
    {
        $this->database = $database;
    }

    /**
     *  Convert a "something" into a value formatted for SQL insertion
     *
     *  @param mixed $value
     *  @return string
     */
    public function getSqlValue($value)
    {
        if ($value === null)
            return 'null';
        else if (is_int($value) || is_float($value))
            return (string) $value;
        else
            return $this->database->quote($value);
    }

    /**
     *  Build an array of ID field => ID value pairs, and generate SQL where clause data from that
     *
     *  @param array $fields ID fields
     *  @param mixed $values ID values to map to fields
     *  @return string
     */
    public function buildWhereClause($classname, array $fields, $values)
    {
        if (!is_array($values))
            $values = array($values);

        if (count($fields) != count($values))
            throw new NF_EDatabaseError('Unmatched ID field length');

        $tr = new NF_Persistence_Translator($classname, $this->database);

        $data = array_combine($fields, $values);
        $sql  = array();
        foreach($data as $f => $v)
        {
            $f = $tr->translateField($f);

            if ($v === null)
                $sql[] = "$f is null";
            else if (is_array($v) && !empty($v))
            {
                $items = array();
                foreach($v as $v2)
                    $items[] = $this->getSqlValue($v2);
                $items = implode(',', $items);

                $sql[] = "$f in ($items)";
            }
            else
                $sql[] = "$f=" . $this->getSqlValue($v);
        }

        return implode(' and ', $sql);
    }

    /**
     * Build a list of ID values to link
     *
     * @param array  $objects
     * @param string $field
     * @param array  $discriminators
     * @return string
     */
    public function buildIdList($objects, $field, $discriminators = null)
    {
        if (!empty($discriminators))
            $objects = array_filter($objects, function($item) use ($discriminators) {
                foreach($discriminators as $d)
                {
                    $field = $d[1];
                    $value = $d[2];
                    if ($item->$field != $value)
                        return false;
                }

                return true;
            });

        $result = array();
        foreach($objects as $object)
        {
            $v = $object->$field;
            if ($v !== null)
                $result[] = $this->database->quote($v);
        }

        return implode(',', array_unique($result));
    }

    /**
     * Generate an insert into statement.
     */
    public function generateInsert($table, $fields, $values, $replace)
    {
        $sqlvalues = array();
        foreach($values as $v)
            $sqlvalues[] = $this->getSqlValue($v);

        $sqlfields = implode(',', $fields);
        $sqlvalues = implode(',', $sqlvalues);

        return ($replace ? 'replace' : 'insert') . " into $table ($sqlfields) values ($sqlvalues)";
    }

    /**
     * Generate an update statement.
     */
    public function generateUpdate($table, $set, $where)
    {
        $table = $this->database->quoteField($table);

        $sqlset = array();
        foreach($set as $k => $v)
            $sqlset[] = $k . '=' . $this->getSqlValue($v);

        $sqlset = implode(', ', $sqlset);

        return "update $table set $sqlset where $where";
    }
}
