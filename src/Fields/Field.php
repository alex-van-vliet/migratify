<?php


namespace AlexVanVliet\Migratify\Fields;


abstract class Field
{
    abstract public function create(string $name): string;
}
