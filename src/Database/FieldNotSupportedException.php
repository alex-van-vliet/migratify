<?php


namespace AlexVanVliet\Migratify\Database;


use Exception;

class FieldNotSupportedException extends Exception
{
    /**
     * FieldNotSupportedException constructor.
     * @param string $type The type of the field.
     */
    public function __construct(string $type)
    {
        parent::__construct("Field type $type not supported");
    }
}
