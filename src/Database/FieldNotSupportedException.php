<?php


namespace AlexVanVliet\Migratify\Database;


use Exception;

class FieldNotSupportedException extends Exception
{
    public function __construct($name)
    {
        parent::__construct("Field type $name not supported");
    }
}
