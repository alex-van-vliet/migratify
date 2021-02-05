<?php


namespace AlexVanVliet\Migratify;

use AlexVanVliet\Migratify\Database\BlueprintMock;
use AlexVanVliet\Migratify\Fields\Field;
use Attribute;

#[Attribute]
class Model
{
    public function __construct(
        protected array $fields,
    )
    {
        foreach ($this->fields as $k => $field) {
            assert(count($field) == 1 or count($field) == 2);
            if (count($field) == 1)
                $this->fields[$k] = new Field($field[0]);
            else
                $this->fields[$k] = new Field($field[0], $field[1]);
        }
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function toBlueprint(string $table)
    {
        $blueprint = new BlueprintMock($table);

        foreach ($this->fields as $name => $field) {
            $stored = $blueprint->{$field->getType()}($name);
            foreach ($field->getAttributes() as $key => $attribute) {
                $stored->{$key}($attribute);
            }
        }

        return $blueprint;
    }
}
