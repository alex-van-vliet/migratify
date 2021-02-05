<?php


namespace AlexVanVliet\Migratify;


use Exception;
use Throwable;

class ModelNotFoundException extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("Model attribute not found for class $class.");
    }
}
