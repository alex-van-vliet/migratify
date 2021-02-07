<?php


namespace AlexVanVliet\Migratify;


use Exception;

class InvalidBaseConfigurationException extends Exception
{
    /**
     * InvalidBaseConfigurationException constructor.
     *
     * @param string $class The name of the class.
     */
    public function __construct(string $class)
    {
        parent::__construct("Invalid base configuration for class $class.");
    }
}
