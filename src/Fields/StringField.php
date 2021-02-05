<?php


namespace AlexVanVliet\Migratify\Fields;


class StringField extends Field
{
    public function __construct(
        protected int $length = 255,
    )
    {
    }

    public function create(string $name): string
    {
        return "\$table->string('$name', {$this->length});";
    }
}
