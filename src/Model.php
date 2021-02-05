<?php


namespace AlexVanVliet\Migratify;

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
                $this->fields[$k] = new $field[0]();
            else
                $this->fields[$k] = new $field[0](...$field[2]);
        }
    }

    public function getFields()
    {
        return $this->fields;
    }
}
