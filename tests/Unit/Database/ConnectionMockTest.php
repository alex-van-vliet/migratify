<?php


namespace AlexVanVliet\Migratify\Tests\Unit\Database;


use AlexVanVliet\Migratify\Database\ConnectionMock;
use AlexVanVliet\Migratify\Tests\TestCase;

class ConnectionMockTest extends TestCase
{
    /** @test */
    function get_schema_builder_returns_the_same_schema_builder()
    {
        $mock = new ConnectionMock();

        $this->assertSame($mock->getSchemaBuilder(), $mock->getSchemaBuilder());
    }
}
