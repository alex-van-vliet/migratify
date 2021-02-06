<?php


namespace AlexVanVliet\Migratify\Database;


use Exception;

class OperationNotSupportedException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct("Operation $name not supported.");
    }
}
