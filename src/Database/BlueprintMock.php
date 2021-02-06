<?php


namespace AlexVanVliet\Migratify\Database;


use AlexVanVliet\Migratify\Fields\Field;
use Closure;
use Illuminate\Database\Schema\Blueprint;

class BlueprintMock extends Blueprint
{
    protected array $fields;

    public function __construct($table, Closure $callback = null)
    {
        parent::__construct($table, $callback);

        $this->fields = [];
    }

    public function addColumn($type, $name, array $parameters = [])
    {
        $this->fields[$name] = new Field($type, $parameters);

        return $this->fields[$name];
    }

    public function removeColumn($name)
    {
        parent::removeColumn($name);

        unset($this->fields[$name]);
    }

    public function getFields()
    {
        return $this->fields;
    }

    protected function addCommand($name, array $parameters = [])
    {
        throw new OperationNotSupportedException($name);
    }

    public function computed($column, $expression)
    {
        throw new FieldNotSupportedException('computed');
    }

    public function morphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('morphs');
    }

    public function nullableMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('nullableMorphs');
    }

    public function numericMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('numericMorphs');
    }

    public function nullableNumericMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('nullableNumericMorphs');
    }

    public function uuidMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('uuidMorphs');
    }

    public function nullableUuidMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('nullableUuidMorphs');
    }
}
