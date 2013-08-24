<?php

/**
 * Helper class that handles mappings between tables
 */
class NF_Persistence_Relation
{
    const Source = 1;
    const Target = 2;

    public $type;
    public $sourceClass;
    public $targetClass;
    public $sourceField;
    public $targetField;
    public $relationClass;
    public $relationSourceField;
    public $relationTargetField;
    public $objectField;
    public $discriminators = array();

    public function addSourceDiscriminator($field, $value)
    {
        $this->discriminators[] = array(self::Source, $field, $value);

        return $this;
    }

    public function addTargetDiscriminator($field, $value)
    {
        $this->discriminators[] = array(self::Target, $field, $value);

        return $this;
    }

    public function getDiscriminators($type)
    {
        $result = array();
        foreach($this->discriminators as $d)
            if ($d[0] == $type)
                $result[] = $d;

        return $result;
    }
}
