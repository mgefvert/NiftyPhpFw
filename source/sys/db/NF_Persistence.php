<?php

require_once __DIR__ . '/helper/NF_Persistence_Map.php';
require_once __DIR__ . '/helper/NF_Persistence_Relation.php';
require_once __DIR__ . '/helper/NF_Persistence_SQL.php';
require_once __DIR__ . '/helper/NF_Persistence_Translator.php';

/**
 * Persistence layer for loading and saving objects in a reasonably transparent
 * way to and from a database
 *
 * Agent Smith: "Why, Mr. Anderson, why? Why do you persist?"
 * Neo:         "Because I choose to."
 *
 * PHP Version 5.3
 *
 * @package    NiftyFramework
 * @author     Mats Gefvert <mats@gefvert.se>
 * @license    http://www.sun.com/cddl/ Common Development and Distribution License
 */
class NF_Persistence
{
    public $database;
    protected $sqlgen;

    // <editor-fold defaultstate="collapsed" desc="Initialization and tool functions">

    /**
     *  Constructor
     */
    public function __construct(NF_IDatabase $database)
    {
        $this->database = $database;
        $this->sqlgen = new NF_Persistence_SQL($this->database);
    }

    /**
     * Tool function for making sure everything works before work
     *
     * @param string $classname
     * @return array
     */
    protected function setup($classname, $translator = false)
    {
        $classname = NF_Filter::name($classname);
        NF_Persistence_Map::getMap($classname, $table, $idfield, $fieldmap);
        $tr = $translator ? new NF_Persistence_Translator($classname, $this->database) : null;

        return array($this->database, $table, $idfield, $fieldmap, $tr);
    }

    protected function abortIfNull($values)
    {
        if (!is_array($values))
            $values = array($values);

        foreach($values as $v)
            if ($v !== null)
                return;

        throw new NF_EDatabaseError('All key values cannot be NULL');
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Table and field mapping">

    /**
     *  Map a class to a table
     *
     *  @param string $classname Class name to map
     *  @param string $table     The name of the table
     *  @param mixed  $id        Which property contains the ID value (not field, object property)
     *  @return void
     */
    public static function mapTable($classname, $table, $id)
    {
        if (!is_array($id))
            $id = explode(';', $id);
        $id = array_filter($id);

        if (empty($id))
            throw new Exception('Table mapping must have a primary index identifier.');

        NF_Persistence_Map::$_tablemap[$classname] = array(
            'table' => $table,
            'id'    => $id
        );

        NF_Persistence_Map::parseFieldDoc($classname);
    }

    /**
     *  Map object properties to fields in the table
     *
     *  @param string $classname Class name to map properties for
     *  @param array  $fieldmap  Associative array of property names => database fields
     *  @return void
     */
    public static function mapFields($classname, $fieldmap)
    {
        NF_Persistence_Map::$_fieldmap[$classname] = $fieldmap;
    }

    /**
     * Map a one-to-one relation between two tables
     *
     * @param string $sourceClass Source class to map
     * @param string $targetClass Target class to map
     * @param string $sourceField Source field to match
     * @param string $targetField Target field to match
     * @param string $objectField A property in the source class that will receive an array of loaded objects
     * @return NF_Persistence_Relation
     */
    public static function mapRelation11($sourceClass, $targetClass, $sourceField, $targetField, $objectField)
    {
        // Internally, we use a M-1 link because it gives us the same result anyway.
        return NF_Persistence_Map::mapRelationM1($sourceClass, $targetClass, $sourceField, $targetField, $objectField);
    }

    /**
     * Map a one-to-many relation between two tables
     *
     * @param string $sourceClass Source class to map
     * @param string $targetClass Target class to map
     * @param string $sourceField Source field to match
     * @param string $targetField Target field to match
     * @param string $objectField A property in the source class that will receive an array of loaded objects
     * @return NF_Persistence_Relation
     */
    public static function mapRelation1M($sourceClass, $targetClass, $sourceField, $targetField, $objectField)
    {
        return NF_Persistence_Map::mapRelation1M($sourceClass, $targetClass, $sourceField, $targetField, $objectField);
    }

    /**
     * Map a many-to-one relation between to tables
     *
     * @param string $sourceClass Source class to map
     * @param string $targetClass Target class to map
     * @param string $sourceField Source field to match
     * @param string $targetField Target field to match
     * @param string $objectField A property in the source class that will receive a loaded object
     * @return NF_Persistence_Relation
     */
    public static function mapRelationM1($sourceClass, $targetClass, $sourceField, $targetField, $objectField)
    {
        return NF_Persistence_Map::mapRelationM1($sourceClass, $targetClass, $sourceField, $targetField, $objectField);
    }

    /**
     * Map a many-to-many relationship between too tables, using a connector table in between
     *
     * @param string $sourceClass         Source class to map
     * @param string $targetClass         Target class to map
     * @param string $sourceField         Source field to match
     * @param string $targetField         Target field to map
     * @param string $objectField         A property in the source class that will receive an array of loaded objects
     * @param string $relationClass       The connector class that sits in-between Source and Target
     * @param string $relationSourceField The field that will match the source table and source field
     * @param string $relationTargetField The field that will match the target table and target field
     * @return NF_Persistence_Relation
     */
    public static function mapRelationMM($sourceClass, $targetClass, $sourceField, $targetField, $objectField,
        $relationClass, $relationSourceField, $relationTargetField)
    {
        return NF_Persistence_Map::mapRelationMM($sourceClass, $targetClass, $sourceField, $targetField, $objectField,
            $relationClass, $relationSourceField, $relationTargetField);
    }

    /**
     * Set a particular field type for a given set of fields.
     *
     * @param string $sourceClass
     * @param int    $fieldType
     * @param array  $fields
     */
    public static function setFieldType($sourceClass, $fieldType, array $fields)
    {
        NF_Persistence_Map::setFieldType($sourceClass, $fieldType, $fields);
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Object loading">

    /**
     *  Try to load an object from the database. If it doesn't exist, return null.
     *
     *  @param string $classname Class name of object to get.
     *  @param mixed  $id        ID value of object
     *  @return object An object if found, null otherwise
     */
    public function tryLoad($classname, $id)
    {
        list(, , $idfields) = $this->setup($classname);

        return current($this->loadByWhereClause($classname, 'where ' . $this->sqlgen->buildWhereClause($classname, $idfields, $id)));
    }

    /**
     *  Load an object from the database, throwing exceptions if we fail.
     *
     *  @param string $classname Class name of object to get.
     *  @param mixed  $id        ID value of object
     *  @return object The found object
     */
    public function load($classname, $id)
    {
        if (($obj = $this->tryLoad($classname, $id)) == null)
        {
            $id    = is_array($id) ? implode(', ', $id) : $id;
            $table = NF_Persistence_Map::getMapTable($classname);
            throw new NF_EDatabaseError("Can't find element '$id' in table '$table'");
        }
        else
            return $obj;
    }

    /**
     *  Load all objects from the database, in key order.
     *
     *  @param string $classname Class name of object to get.
     *  @return array The objects from the database
     */
    public function loadAll($classname)
    {
        list(, , $idfields) = $this->setup($classname);

        return $this->loadByWhereClause($classname,
            'order by ' . implode(',', array_map(function($s) { return "[$s]"; }, $idfields)));
    }

    /**
     * Load objects from the database, based on one or more fields
     *
     * @param string $classname Class name of object to get
     * @param array  $fields    Fields to match on: field => value
     * @return array
     */
    public function loadByFields($classname, array $fields)
    {
        if (empty($fields))
            return array();

        return $this->loadByWhereClause($classname,
            'where ' . $this->sqlgen->buildWhereClause($classname, array_keys($fields), array_values($fields)));
    }

    /**
     *  Load objects from the database, with "where" or "order by" statements
     *
     *  @param string $classname Class name of object to get
     *  @param string $where     Additional clauses to be appended to the query. May start with "where", "order by" or any other
                                 SQL-specific statement.
     *  @return array The objects from the database
     */
    public function loadByWhereClause($classname, $where)
    {
        list($db, $table, , $fieldmap) = $this->setup($classname);

        $vars   = array_keys(get_class_vars($classname));
        $fields = array();
        foreach($vars as $v)
        {
            $fields[] = isset($fieldmap[$v])
                ? $db->quoteField($fieldmap[$v]) . ' as ' . $db->quoteField($v)
                : $db->quoteField($v);
        }
        $fields = implode(', ', $fields);
        $query  = "select $fields from $table $where";

        $tr     = new NF_Persistence_Translator($classname, $db);
        $data   = $db->query($tr->translate($query));
        $result = $data->fetchAllAsObjects($classname);

        NF_Persistence_Map::processLoadedTypes($result);

        if (in_array('loaded', get_class_methods($classname)))
            foreach($result as $obj)
                $obj->loaded();

        $data->close();

        return $result;
    }

    /**
     *  Load objects from the database, with complete control over the SQL
     *  query, giving fields, "join" clauses as necessary. QueryTranslate will
     *  be used to translate the object syntax into SQL syntax.
     *
     *  @param string $classname Class name of object to get
     *  @param string $query     Query to run.
     *  @return array The objects from the database
     */
    public function loadByQuery($classname, $query)
    {
        list($db, , , $fieldmap, $tr) = $this->setup($classname, true);

        $fieldmap = array_flip($fieldmap);
        $data     = $db->query($tr->translate($query));

        $info   = $data->getFieldInfo();
        $fields = array();
        foreach($info as $k)
        {
            $k        = $k['name'];
            $fields[] = isset($fieldmap[$k]) ? $fieldmap[$k] : $k;
        }

        $types      = NF_Persistence_Map::getFieldTypes($classname);
        $result     = array();
        $has_loaded = in_array('loaded', get_class_methods($classname));
        while (($row = $data->fetchValues()))
        {
            $obj = new $classname();
            foreach ($fields as $k => $field)
                $obj->$field = $row[$k];

            $result[] = $obj;
        }
        $data->close();

        NF_Persistence_Map::processLoadedTypes($result, $types);

        if ($has_loaded)
            foreach($result as $obj)
                $obj->loaded();

        return $result;
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Saving and table manipulation">

    /**
     * Tool function for inserting or replacing a record into a table
     */
    protected function doInsert($obj, $replace)
    {
        $classname = get_class($obj);
        list($db, $table, , , $tr) = $this->setup($classname, true);

        if (method_exists($obj, 'persist'))
            $obj->persist();

        $items  = array();
        $values = array();
        $fields = get_class_vars($classname);
        $types  = NF_Persistence_Map::getFieldTypes($classname);
        foreach (get_object_vars($obj) as $k => $v)
            if (array_key_exists($k, $fields))
            {
                $items[]  = $tr->translateField($k);
                $values[] = NF_Persistence_Map::processSaveType($v, isset($types[$k]) ? $types[$k] : null);
            }

        $db->execute($this->sqlgen->generateInsert($table, $items, $values, $replace));

        return $db->lastInsertId();
    }

    /**
     *  Insert a new object into the database. This will generate an INSERT INTO
     *  sql statement.
     *
     *  @param object $obj Object to insert. Its class must have been mapped to a table.
     *  @return mixed The last inserted ID value -- if any such was generated.
     */
    public function insert($obj)
    {
        return $this->doInsert($obj, false);
    }

    /**
     *  "Replace" a new object into the database. This will generate an REPLACE INTO
     *  sql statement, which is MySQL-specific.
     *
     *  @param object $obj Object to insert. Its class must have been mapped to a table.
     *  @return mixed The last inserted ID value -- if any such was generated.
     */
    public function replace($obj)
    {
        return $this->doInsert($obj, true);
    }

    /**
     *  Save an object into the database. This will generate an UPDATE ... SET
     *  WHERE id=... sql statement, updating the existing row with new data.
     *
     *  @param object $obj Object to save. Its class must have been mapped to a table.
     *  @return void
     */
    public function save($obj)
    {
        $classname = get_class($obj);
        list($db, $table, $idfields, , $tr) = $this->setup($classname, true);

        if (method_exists($obj, 'persist'))
            $obj->persist();

        $idvalues = array_map(function($f) use ($obj) { return $obj->$f; }, $idfields);
        $this->abortIfNull($idvalues);

        $set    = array();
        $fields = get_class_vars($classname);
        $types  = NF_Persistence_Map::getFieldTypes($classname);
        foreach (get_object_vars($obj) as $k => $v)
            if (!in_array($k, $idfields) && array_key_exists($k, $fields))
                $set[$tr->translateField($k)] = NF_Persistence_Map::processSaveType($v, isset($types[$k]) ? $types[$k] : $v);

        if (empty($set))
            throw new NF_EDatabaseError('Cannot save instances of class ' . $classname .
                    ' since the primary key covers the entire data set. Use insert or replace instead.');

        $where = $this->sqlgen->buildWhereClause($classname, $idfields, $idvalues);
        $sql   = $this->sqlgen->generateUpdate($table, $set, $where);

        $db->execute($sql);
    }

    /**
     *  Delete an instantiated object from the database.
     *
     *  @param object $obj Object to delete. Its class must have been mapped to a table.
     *  @return void
     */
    public function deleteObject($obj)
    {
        list($db, $table, $idfields) = $this->setup(get_class($obj));

        $idvalues = array_map(function($f) use ($obj) { return $obj->$f; }, $idfields);
        $this->abortIfNull($idvalues);

        $db->execute(
            'delete from ' . $db->quoteField($table) .
            ' where ' . $this->sqlgen->buildWhereClause(get_class($obj), $idfields, $idvalues)
        );
    }

    /**
     *  Delete a specific object from the database, without having it instantiated.
     *
     *  @param string $classname Class of the object to delete. It must have been mapped to a table.
     *  @param mixed  $id        ID value of the object to delete.
     *  @return void
     */
    public function delete($classname, $id)
    {
        list($db, $table, $idfields) = $this->setup($classname);
        $this->abortIfNull($id);

        $db->execute(
            'delete from ' . $db->quoteField($table) .
            ' where ' . $this->sqlgen->buildWhereClause($classname, $idfields, $id)
        );
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="SQL Translation functions">

    /**
     *  Shortcut to translate a query from object syntax to SQL syntax.
     *
     *  @param string $classname Name of class to default to
     *  @param string $query     Query to translate
     *  @return string
     */
    public function sql($classname, $query)
    {
        $tr = new NF_Persistence_Translator($classname, $this->database);
        return $tr->translate($query);
    }

    /**
     *  Run a query in object syntax on the database, giving a result set.
     *
     *  @param string       $classname Name of class to default to
     *  @param string       $query     Object-syntax query to run
     *  @return NF_IResult
     */
    public function query($classname, $query)
    {
        return $this->database->query($this->sql($classname, $query));
    }

    /**
     *  Run a query in object syntax on the database, return all data as arrays.
     *
     *  @param string       $classname Name of class to default to
     *  @param string       $query     Object-syntax query to run
     *
     *  @return array
     */
    public function queryAllAsArray($classname, $query)
    {
        return $this->database->queryAllAsArray($this->sql($classname, $query));
    }

    /**
     *  Run a query in object syntax on the database, return all data as objects.
     *
     *  @param string       $classname Name of class to default to
     *  @param string       $query     Object-syntax query to run
     *
     *  @return array
     */
    public function queryAllAsObjects($classname, $query)
    {
        return $this->database->queryAllAsObjects($this->sql($classname, $query));
    }

    /**
     *  Run a query in object syntax on the database, return a single array of
     *  values from the first field of the result set.
     *
     *  @param string       $classname Name of class to default to
     *  @param string       $query     Object-syntax query to run
     *
     *  @return array
     */
    public function querySingleValueArray($classname, $query)
    {
        return $this->database->querySingleValueArray($this->sql($classname, $query));
    }

    /**
     *  Run a query in object syntax on the database, returning a scalar value
     *
     *  @param string       $classname Name of class to default to
     *  @param string       $query     Object-syntax query to run
     *
     *  @return mixed
     */
    public function queryScalar($classname, $query)
    {
        return $this->database->queryScalar($this->sql($classname, $query));
    }

    /**
     *  Run a query in object syntax on the database, returning an associative array
     *  of lookup field => value items.
     *
     *  @param string       $classname Name of class to default to
     *  @param string       $query     Object-syntax query to run
     *
     *  @return array
     */
    public function queryLookup($classname, $query)
    {
        return $this->database->queryLookup($this->sql($classname, $query));
    }

    /**
     *  Run a query in object syntax on the database, without returning results.
     *
     *  @param string       $classname Name of class to default to
     *  @param string       $query     Object-syntax query to run
     *
     *  @return void
     */
    public function execute($classname, $query)
    {
        return $this->database->execute($this->sql($classname, $query));
    }

    // </editor-fold>

    // <editor-fold defaultstate="collapsed" desc="Object relation loading">

    /**
     * Function for loading data according to a specific relation
     */
    protected function loadByRelation(array $data, NF_Persistence_Relation $relation, $filter)
    {
        if ($filter)
            $filter = " and ($filter)";

        $discriminators = $relation->getDiscriminators(NF_Persistence_Relation::Target);
        foreach($discriminators as $d)
        {
            $field = "[{$d[1]}]";
            $value = $this->sqlgen->getSqlValue($d[2]);
            $filter .= " and ($field=$value)";
        }

        $objects = array();

        if ($relation->relationClass)
        {
            // ManyToMany relation
            class_exists($relation->relationClass);
            class_exists($relation->targetClass);

            // Load relation objects
            $id = $this->sqlgen->buildIdList($data, $relation->sourceField, $relation->getDiscriminators(NF_Persistence_Relation::Source));
            if ($id != '')
            {
                $relationObjects = $this->loadByWhereClause($relation->relationClass, "where [{$relation->relationSourceField}] in ($id)");

                // Load target objects
                $id = $this->sqlgen->buildIdList($relationObjects, $relation->relationTargetField);
                if ($id != '')
                    $objects = $this->loadByWhereClause($relation->targetClass, "where [{$relation->targetField}] in ($id)" . $filter);
            }
        }
        else
        {
            // OneToMany or ManyToOne relation
            class_exists($relation->targetClass);

            $id = $this->sqlgen->buildIdList($data, $relation->sourceField, $relation->getDiscriminators(NF_Persistence_Relation::Source));
            if ($id != '')
                $objects = $this->loadByWhereClause($relation->targetClass, "where [{$relation->targetField}] in ($id)" . $filter);
        }

        if (count($objects) > 0)
        {
            switch($relation->type)
            {
                case NF_Persistence_Map::OneToMany:
                    $this->tieObjects1M($data, $objects, $relation);
                    break;

                case NF_Persistence_Map::ManyToOne:
                    $this->tieObjectsM1($data, $objects, $relation);
                    break;

                case NF_Persistence_Map::ManyToMany:
                    $this->tieObjectsMM($data, $relationObjects, $objects, $relation);
                    break;
            }
        }
        else
        {
            $objfield = $relation->objectField;
            foreach($data as $item)
                $item->$objfield = $relation->type == NF_Persistence_Map::ManyToOne ? null : array();
        }

        return $objects;
    }

    /**
     * Tie loaded objects to original using a one-to-many mapping
     */
    protected function tieObjects1M($src, $dest, NF_Persistence_Relation $relation)
    {
        $srcfield = $relation->sourceField;
        $destfield = $relation->targetField;
        $objfield = $relation->objectField;

        $dest = NF_Arrays::group($dest, $destfield);

        foreach($src as $obj)
        {
            $v = $obj->$srcfield;
            $obj->$objfield = ($v !== null && isset($dest[$v]))
                ? $dest[$v]
                : array();
        }
    }

    /**
     * Tie loaded objects to original using a many-to-one mapping
     */
    protected function tieObjectsM1($src, $dest, NF_Persistence_Relation $relation)
    {
        $srcfield = $relation->sourceField;
        $destfield = $relation->targetField;
        $objfield = $relation->objectField;

        $dest = NF_Arrays::hash($dest, $destfield);

        foreach($src as $obj)
        {
            $v = $obj->$srcfield;
            $obj->$objfield = ($v !== null && isset($dest[$v]))
                ? $dest[$v]
                : null;
        }
    }

    /**
     * Tie loaded objects to original using a many-to-many mapping
     */
    protected function tieObjectsMM($src, $relObjs, $dest, NF_Persistence_Relation $relation)
    {
        $srcfield  = $relation->sourceField;
        $destfield = $relation->targetField;
        $objfield  = $relation->objectField;
        $relfield1 = $relation->relationSourceField;
        $relfield2 = $relation->relationTargetField;

        $dest = NF_Arrays::hash($dest, $destfield);

        foreach($src as $obj)
        {
            $v = $obj->$srcfield;

            $map = array();
            foreach($relObjs as $r)
                if ($r->$relfield1 == $v && isset($dest[$r->$relfield2]))
                    $map[] = $dest[$r->$relfield2];

            $obj->$objfield = $map;
        }
    }

    /**
     * Loads all related objects given a relation between two classes.
     * The target can be either a destination class name, which the system
     * will proceed to load, or an object property of the source class.
     *
     * @param mixed $data    Object or array of objects
     * @param mixed $target Classname or object property, or array of classnames/properties
     * @return array
     */
    public function loadRelated($data, $target, $filter = '')
    {
        if (empty($data))
            return array();
        if (is_object($data))
            $data = array($data);

        if (is_array($data))
        {
            if (count($data) == 0)
                return array();
            $sourceclass = get_class(reset($data));
        }
        else
            throw new NF_EDatabaseError('loadRelated parameter must be object or array of objects');

        if (!is_array($target))
            $target = array($target);
        $relmaps = array();
        foreach($target as $targetItem)
        {
            $relmap = NF_Persistence_Map::findRelation($sourceclass, $targetItem);
            if (empty($relmap))
                throw new NF_EDatabaseError("Cannot find mapping between $sourceclass and $targetItem");
            $relmaps = array_merge($relmaps, $relmap);
        }

        $result = null;
        foreach($relmaps as $rel)
        {
            $x = $this->loadByRelation($data, $rel, $filter);
            if ($result == null)
                $result = $x;
        }

        return $result;
    }

    // </editor-fold>

}
