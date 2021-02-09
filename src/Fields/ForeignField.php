<?php


namespace AlexVanVliet\Migratify\Fields;


class ForeignField extends Field
{
    /**
     * ForeignField constructor.
     * @param string $name The name of the column.
     * @param string $type The type of the field.
     * @param array $attributes Its attributes.
     * @param array $options The options.
     */
    public function __construct(
        protected string $name,
        string $type,
        array $attributes = [],
        array $options = [],
    )
    {
        parent::__construct($type, $attributes, $options);
    }
}
