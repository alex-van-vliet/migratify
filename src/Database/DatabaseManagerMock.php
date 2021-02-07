<?php


namespace AlexVanVliet\Migratify\Database;


class DatabaseManagerMock
{
    /**
     * @var ConnectionMock The mocked connection.
     */
    protected ConnectionMock $mockedConnection;

    /**
     * DatabaseManagerMock constructor.
     */
    public function __construct()
    {
        $this->mockedConnection = new ConnectionMock();
    }

    /**
     * Get the mocked connection.
     *
     * @return ConnectionMock
     */
    public function connection(): ConnectionMock
    {
        assert(count(func_get_args()) == 0);
        return $this->mockedConnection;
    }
}
