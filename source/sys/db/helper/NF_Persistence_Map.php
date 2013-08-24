<?php

/**
 * Helper class for mapping between objects and tables
 */
class NF_Persistence_Map
{
    const OneToMany  = 0;
    const ManyToOne  = 1;
    const ManyToMany = 2;

    public static $_tablemap = array();
    public static $_fieldmap = array();
    public static $_fieldtypemap = array();
    public static $_relmap = array();

    protected static $_fieldtypes = array(
        'int', 'string', 'float', 'date', 'time',
        'datetime', 'datetime-utc', 'datetime-utc-fixed',
        'timestamp'
    );

    public static function findRelation($sourceClass, $target)
    {
        if (!isset($sourceClass))
            throw new NF_EDatabaseError("Class $sourceClass is not mapped to any table relations");

        $relmap = self::$_relmap[$sourceClass];

        $result = array_filter($relmap, function($item) use ($target) { return $item->objectField == $target; });
        if (!empty($result))
            return $result;

        $result = array_filter($relmap, function($item) use ($target) { return $item->targetClass == $target; });
        if (!empty($result))
            return $result;

        return null;
    }

    public static function getFieldTypes($classname)
    {
        return array_key_exists($classname, self::$_fieldtypemap)
                ? self::$_fieldtypemap[$classname]
                : array();
    }

    public static function getMap($classname, &$table, &$idField, &$fieldmap)
    {
        class_exists($classname);
        if (!isset(self::$_tablemap[$classname]))
            throw new NF_EDatabaseError("NF_Persistence::getmap - Class $classname is not mapped to a table.");

        $table   = self::$_tablemap[$classname]['table'];
        $idField = self::$_tablemap[$classname]['id'];

        $fieldmap = isset(self::$_fieldmap[$classname]) ? self::$_fieldmap[$classname] : array();
    }

    public static function getMapTable($classname)
    {
        if (!isset(self::$_tablemap[$classname]))
            throw new NF_EDatabaseError("Class $classname is not mapped to a table.");

        return self::$_tablemap[$classname]['table'];
    }

    public static function mapRelation1M($sourceClass, $targetClass, $sourceField, $targetField, $objectField)
    {
        if (is_array($sourceField) || is_array($targetField) ||
            strpos($sourceField, ';') !== false || strpos($targetField, ';') !== false)
            throw new NF_EDatabaseError("Relations must be mapped on unique fields");

        $r = new NF_Persistence_Relation();
        $r->type        = self::OneToMany;
        $r->sourceClass = $sourceClass;
        $r->sourceField = $sourceField;
        $r->targetClass = $targetClass;
        $r->targetField = $targetField;
        $r->objectField = $objectField;

        self::$_relmap["$sourceClass"][] = $r;

        return $r;
    }

    public static function mapRelationM1($sourceClass, $targetClass, $sourceField, $targetField, $objectField)
    {
        if (is_array($sourceField) || is_array($targetField) ||
            strpos($sourceField, ';') !== false || strpos($targetField, ';') !== false)
            throw new NF_EDatabaseError("Relations must be mapped on unique fields");

        $r = new NF_Persistence_Relation();
        $r->type        = self::ManyToOne;
        $r->sourceClass = $sourceClass;
        $r->sourceField = $sourceField;
        $r->targetClass = $targetClass;
        $r->targetField = $targetField;
        $r->objectField = $objectField;

        self::$_relmap["$sourceClass"][] = $r;

        return $r;
    }

    public static function mapRelationMM($sourceClass, $targetClass, $sourceField, $targetField, $objectField,
        $relationClass, $relationSourceField, $relationTargetField)
    {
        $r = new NF_Persistence_Relation();
        $r->type                = self::ManyToMany;
        $r->sourceClass         = $sourceClass;
        $r->sourceField         = $sourceField;
        $r->targetClass         = $targetClass;
        $r->targetField         = $targetField;
        $r->objectField         = $objectField;
        $r->relationClass       = $relationClass;
        $r->relationSourceField = $relationSourceField;
        $r->relationTargetField = $relationTargetField;

        self::$_relmap["$sourceClass"][] = $r;

        return $r;
    }

    public static function parseFieldDoc($classname)
    {
        $fields = array();
        $map = array();

        $class = new ReflectionClass($classname);
        foreach($class->getProperties() as $prop)
            if (($doc = $prop->getDocComment()) != '')
                if (preg_match_all('/@(persist[\w-]+) ([\w-]+)/i', $doc, $matches, PREG_SET_ORDER))
                    foreach($matches as $match)
                    {
                        list(, $k, $v) = $match;

                        if (eqcase($k, 'persist-type'))
                            $fields[$v][] = strtolower($prop->name);

                        if (eqcase($k, 'persist-map'))
                            $map[$prop->name] = $v;
                    }

        if (!empty($fields))
            foreach($fields as $type => $_fields)
                self::setFieldType($classname, $type, $_fields);

        if (!empty($map))
            self::$_fieldmap[$classname] = $map;
    }

    public static function processLoadedTypes(array $objects)
    {
        if (empty($objects))
            return;

        $types = NF_Persistence_Map::getFieldTypes(get_class(reset($objects)));
        if (empty($types))
            return;

        foreach($objects as $obj)
            foreach($types as $key => $type)
            {
                $value = &$obj->$key;
                switch($type)
                {
                    case 'int':
                        if ($value !== null && !is_int($value))
                            $value = (int)$value;
                        break;

                    case 'float':
                        if ($value !== null && !is_int($value) && !is_double($value))
                            $value = floatval($value);
                        break;

                    case 'string':
                        if ($value !== null && !is_string($value))
                            $value = (string)$value;
                        break;

                    case 'date':
                        $value = new NF_Date($value);
                        break;

                    case 'time':
                        $value = new NF_Time($value);
                        break;

                    case 'datetime':
                        $value = new NF_DateTime($value);
                        break;

                    case 'datetime-utc':
                        $value = new NF_DateTime($value, NF_TimeZone::utc());
                        $value = $value->toLocal();
                        break;

                    case 'datetime-utc-fixed':
                        $value = new NF_DateTime($value, NF_TimeZone::utc());
                        break;

                    case 'timestamp':
                        $value = $value !== null ? NF_DateTime::fromTimestamp($value) : new NF_DateTime();
                        break;
                }
            }

        unset($value);
    }

    public static function processSaveType($value, $type)
    {
        $objvalue = is_object($value) && method_exists($value, 'toValue') ? $value->toValue() : $value;
        if ($objvalue === null)
            return;

        switch($type)
        {
            case 'int':
                if (!is_numeric($objvalue))
                    throw new NF_EDatabaseError("Cannot convert '$objvalue' to a numeric value.");
                return (int)$objvalue;

            case 'float':
                if (!is_numeric($objvalue))
                    throw new NF_EDatabaseError("Cannot convert '$objvalue' to a numeric value.");
                return floatval($objvalue);

            case 'string':
                return (string)$objvalue;

            case 'date':
                if (!($value instanceof NF_Date))
                    $value = new NF_Date($value);
                return (string)$value;

            case 'time':
                if (!($value instanceof NF_Time))
                    $value = new NF_Time($value);
                return (string)$value;

            case 'datetime':
                if (!($value instanceof NF_DateTime))
                    $value = new NF_DateTime($value);
                return (string)$value->toLocal();

            case 'datetime-utc-fixed':
            case 'datetime-utc':
                if (!($value instanceof NF_DateTime))
                    $value = new NF_DateTime($value);
                return (string)$value->toUtc();

            case 'timestamp':
                if (!($value instanceof NF_DateTime))
                    $value = new NF_DateTime($value);
                return (int)$value->getTimestamp();

            default:
                return (is_int($value) || is_double($value))
                    ? $value
                    : (string)$value;
        }
    }

    public static function setFieldType($sourceClass, $fieldType, array $fields)
    {
        if (!in_array($fieldType, self::$_fieldtypes))
            throw new NF_EDatabaseError("Invalid field type $fieldType for class $sourceClass");

        foreach($fields as $field)
            self::$_fieldtypemap[$sourceClass][$field] = $fieldType;
    }
}
