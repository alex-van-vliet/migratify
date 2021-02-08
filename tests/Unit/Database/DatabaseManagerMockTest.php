<?php


namespace AlexVanVliet\Migratify\Tests\Unit\Database;


use AlexVanVliet\Migratify\Database\ConnectionMock;
use AlexVanVliet\Migratify\Database\DatabaseManagerMock;
use AlexVanVliet\Migratify\Tests\TestCase;

class DatabaseManagerMockTest extends TestCase
{
    /** @test */
    function get_schema_builder_returns_the_same_connection()
    {
        $mock = new DatabaseManagerMock();

        $this->assertSame($mock->connection(), $mock->connection());
    }
}
