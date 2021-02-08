<?php


namespace AlexVanVliet\Migratify\Tests\Unit\Database;


use AlexVanVliet\Migratify\Database\BlueprintMock;
use AlexVanVliet\Migratify\Database\SchemaBuilderMock;
use AlexVanVliet\Migratify\Tests\TestCase;

class SchemaBuilderMockTest extends TestCase
{
    protected SchemaBuilderMock $mock;

    public function setUp(): void
    {
        parent::setUp();

        $this->mock = new SchemaBuilderMock();
    }

    /** @test */
    function create_runs_the_callback_on_the_blueprint()
    {
        $this->mock->create('users', fn($table) => $table->text('column'));

        $this->assertArrayHasKey('users', $this->mock->getBlueprints());

        $expected = new BlueprintMock('users', fn($table) => $table->text('column'));
        $this->assertEquals($expected, $this->mock->getBlueprints()['users']);
    }

    /** @test */
    function create_can_be_ran_several_times()
    {
        $this->mock->create('users', fn($table) => $table->text('column_1'));
        $this->mock->create('users', fn($table) => $table->text('column_2'));

        $expected = new BlueprintMock('users', function($table) {
            $table->text('column_1');
            $table->text('column_2');
        });
        $this->assertEquals($expected, $this->mock->getBlueprints()['users']);
    }

    /** @test */
    function table_runs_the_callback_on_the_blueprint()
    {
        $this->mock->table('users', fn($table) => $table->text('column'));

        $this->assertArrayHasKey('users', $this->mock->getBlueprints());

        $expected = new BlueprintMock('users', fn($table) => $table->text('column'));
        $this->assertEquals($expected, $this->mock->getBlueprints()['users']);
    }

    /** @test */
    function table_can_be_ran_several_times()
    {
        $this->mock->table('users', fn($table) => $table->text('column_1'));
        $this->mock->table('users', fn($table) => $table->text('column_2'));

        $expected = new BlueprintMock('users', function($table) {
            $table->text('column_1');
            $table->text('column_2');
        });
        $this->assertEquals($expected, $this->mock->getBlueprints()['users']);
    }
}
