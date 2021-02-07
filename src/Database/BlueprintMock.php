<?php


namespace AlexVanVliet\Migratify\Database;


use AlexVanVliet\Migratify\Fields\Field;
use Closure;
use Illuminate\Database\Schema\Blueprint;

class BlueprintMock extends Blueprint
{
    /**
     * @var array The list of fields at the end.
     */
    protected array $fields;

    /**
     * BlueprintMock constructor.
     *
     * @param string $table The name of the table.
     * @param Closure|null $callback The blueprint callback.
     */
    public function __construct($table, Closure $callback = null)
    {
        parent::__construct($table, $callback);

        $this->fields = [];
    }

    /**
     * Add a column.
     *
     * @param string $type The type of the column.
     * @param string $name The name of the column.
     * @param array $parameters Parameters
     * @return Field
     */
    public function addColumn($type, $name, array $parameters = [])
    {
        parent::addColumn($type, $name, $parameters);

        $this->fields[$name] = new Field($type, $parameters);

        return $this->fields[$name];
    }

    /**
     * Remove a column.
     *
     * @param string $name The name of the column.
     * @return void
     */
    public function removeColumn($name)
    {
        parent::removeColumn($name);

        unset($this->fields[$name]);
    }

    /**
     * Get the list of fields.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Make commands not supported.
     *
     * @param string $name The name of the command
     * @param array $parameters The parameters
     * @return void
     * @throws OperationNotSupportedException
     */
    protected function addCommand($name, array $parameters = [])
    {
        throw new OperationNotSupportedException($name);
    }

    /**
     * Make computed field not supported.
     *
     * @param string $column The name of the column.
     * @param string $expression The expression.
     * @return void
     * @throws FieldNotSupportedException
     */
    public function computed($column, $expression)
    {
        throw new FieldNotSupportedException('computed');
    }

    /**
     * Make morphs field not supported.
     *
     * @param string $name The name of the column.
     * @param string $indexName The name of the index.
     * @return void
     * @throws FieldNotSupportedException
     */
    public function morphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('morphs');
    }

    /**
     * Make nullableMorphs field not supported.
     *
     * @param string $name The name of the column.
     * @param string $indexName The name of the index.
     * @return void
     * @throws FieldNotSupportedException
     */
    public function nullableMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('nullableMorphs');
    }

    /**
     * Make numericMorphs field not supported.
     *
     * @param string $name The name of the column.
     * @param string $indexName The name of the index.
     * @return void
     * @throws FieldNotSupportedException
     */
    public function numericMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('numericMorphs');
    }

    /**
     * Make nullableNumericMorphs field not supported.
     *
     * @param string $name The name of the column.
     * @param string $indexName The name of the index.
     * @return void
     * @throws FieldNotSupportedException
     */
    public function nullableNumericMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('nullableNumericMorphs');
    }

    /**
     * Make uuidMorphs field not supported.
     *
     * @param string $name The name of the column.
     * @param string $indexName The name of the index.
     * @return void
     * @throws FieldNotSupportedException
     */
    public function uuidMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('uuidMorphs');
    }

    /**
     * Make nullableUuidMorphs field not supported.
     *
     * @param string $name The name of the column.
     * @param string $indexName The name of the index.
     * @return void
     * @throws FieldNotSupportedException
     */
    public function nullableUuidMorphs($name, $indexName = null)
    {
        throw new FieldNotSupportedException('nullableUuidMorphs');
    }
}
