<?php


namespace AlexVanVliet\Migratify\Database;


class ConnectionMock
{
    protected SchemaBuilderMock $mockedSchemaBuilder;

    public function __construct()
    {
        $this->mockedSchemaBuilder = new SchemaBuilderMock();
    }

    public function getSchemaBuilder()
    {
        assert(count(func_get_args()) == 0);
        return $this->mockedSchemaBuilder;
    }
}
