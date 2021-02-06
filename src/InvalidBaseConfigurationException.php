<?php


namespace AlexVanVliet\Migratify;


use Exception;

class InvalidBaseConfigurationException extends Exception
{
    public function __construct(string $class)
    {
        parent::__construct("Invalid base configuration for class $class.");
    }
}
