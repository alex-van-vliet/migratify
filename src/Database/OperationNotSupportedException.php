<?php


namespace AlexVanVliet\Migratify\Database;


use Exception;

class OperationNotSupportedException extends Exception
{
    /**
     * OperationNotSupportedException constructor.
     * @param string $name The name of the operation.
     */
    public function __construct(string $name)
    {
        parent::__construct("Operation $name not supported.");
    }
}
