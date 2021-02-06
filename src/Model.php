<?php


namespace AlexVanVliet\Migratify;

use AlexVanVliet\Migratify\Database\BlueprintMock;
use AlexVanVliet\Migratify\Fields\Field;
use Attribute;
use ReflectionClass;

#[Attribute]
class Model
{
    public function __construct(
        protected array $fields,
    )
    {
        foreach ($this->fields as $k => $field) {
            assert(count($field) == 1 or count($field) == 2);
            $this->fields[$k] = new Field(...$field);
        }
    }

    public static function from_attribute(string $model): self
    {
        $reflectionClass = new ReflectionClass($model);

        $attributes = $reflectionClass->getAttributes(self::class);
        if (empty($attributes))
            throw new ModelNotFoundException($reflectionClass->getName());
        assert(count($attributes) == 1);

        return $attributes[0]->newInstance();
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
                if (is_int($key)) {
                    $stored->{$attribute}();
                } else {
                    $stored->{$key}($attribute);
                }
            }
        }

        return $blueprint;
    }
}
