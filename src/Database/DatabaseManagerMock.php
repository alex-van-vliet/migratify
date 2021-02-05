<?php


namespace AlexVanVliet\Migratify\Database;


class DatabaseManagerMock
{
    protected ConnectionMock $mockedConnection;

    public function __construct()
    {
        $this->mockedConnection = new ConnectionMock();
    }

    public function connection()
    {
        assert(count(func_get_args()) == 0);
        return $this->mockedConnection;
    }
}
