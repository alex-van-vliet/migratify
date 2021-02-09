<?php


namespace AlexVanVliet\Migratify\Tests\Unit\Database;


use AlexVanVliet\Migratify\Database\BlueprintMock;
use AlexVanVliet\Migratify\Database\OperationNotSupportedException;
use AlexVanVliet\Migratify\Fields\Field;
use AlexVanVliet\Migratify\Fields\ForeignField;
use AlexVanVliet\Migratify\Tests\TestCase;

class BlueprintMockTest extends TestCase
{
    protected BlueprintMock $mock;

    public function setUp(): void
    {
        parent::setUp();

        $this->mock = new BlueprintMock('table');
    }

    /** @test */
    function add_column_also_adds_a_field()
    {
        $this->mock->addColumn(Field::STRING, 'column', ['length' => 255]);

        $this->assertEquals([
            'column' => new Field(Field::STRING, ['length' => 255]),
        ], $this->mock->getFields());
    }

    /** @test */
    function add_column_returns_the_field()
    {
        $this->mock->addColumn(Field::STRING, 'column', ['length' => 255])->nullable();

        $this->assertEquals([
            'column' => new Field(Field::STRING, ['length' => 255, 'nullable' => true]),
        ], $this->mock->getFields());
    }

    /** @test */
    function remove_column_removes_the_column()
    {
        $this->mock->addColumn(Field::STRING, 'column', ['length' => 255]);
        $this->mock->removeColumn('column');

        $this->assertEquals([], $this->mock->getFields());
    }

    /** @test */
    function foreign_id_adds_a_foreign_field()
    {
        $this->mock->foreignId('user_id');

        $this->assertEquals([
            'user_id' => new ForeignField('user_id', ForeignField::BIG_INTEGER, ['autoIncrement' => false, 'unsigned' => true]),
        ], $this->mock->getFields());
    }
}
