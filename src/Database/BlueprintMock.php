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
}
