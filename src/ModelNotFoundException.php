<?php


namespace AlexVanVliet\Migratify;


use Exception;
use Throwable;

class ModelNotFoundException extends Exception
{
    /**
     * ModelNotFoundException constructor.
     *
     * @param string $class The name of the class.
     */
    public function __construct(string $class)
    {
        parent::__construct("Model attribute not found for class $class.");
    }
}
