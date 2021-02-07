<?php


namespace AlexVanVliet\Migratify\Database;


class ConnectionMock
{
    /**
     * @var SchemaBuilderMock The mocked schema builder.
     */
    protected SchemaBuilderMock $mockedSchemaBuilder;

    /**
     * ConnectionMock constructor.
     */
    public function __construct()
    {
        $this->mockedSchemaBuilder = new SchemaBuilderMock();
    }

    /**
     * Get the schema builder.
     *
     * @return SchemaBuilderMock
     */
    public function getSchemaBuilder(): SchemaBuilderMock
    {
        assert(count(func_get_args()) == 0);
        return $this->mockedSchemaBuilder;
    }
}
