<?php


namespace AlexVanVliet\Migratify\Fields;


class StringField extends Field
{
    public function create(string $name): string
    {
        return "\$table->string('$name');";
    }
}
