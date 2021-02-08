<?php

namespace AlexVanVliet\Migratify\Tests\Unit;

use AlexVanVliet\Migratify\Console\Commands\MigrationCreator;
use AlexVanVliet\Migratify\Fields\Field;
use AlexVanVliet\Migratify\Tests\TestCase;
use Illuminate\Support\Facades\File;

class MigrationCreatorTest extends TestCase
{
    protected MigrationCreator $creator;

    public function setUp(): void
    {
        parent::setUp();

        $this->creator = new MigrationCreator(app()->make('files'), app()->basePath('stubs'));

        File::deleteDirectory(database_path('migrations'), true);
    }

    /** @test */
    function create_creates_a_create_migration()
    {
        $path = $this->creator->createMigration('users', [
            'column' => new Field(Field::STRING, ['length' => 255, 'nullable']),
        ]);

        $this->assertStringEndsWith('create_users_table.php', $path);
        $this->assertStringStartsWith(database_path('migrations'), $path);

        $this->assertFileEquals(__DIR__ . '/MigrationCreatorTest.create_creates_a_create_migration.txt', $path);
    }

    /** @test */
    function update_creates_an_update_migration()
    {
        $path = $this->creator->updateMigration('users', [
            'updated_column' => [
                new Field(Field::STRING, ['length' => 128, 'nullable']),
                new Field(Field::STRING, ['length' => 255, 'nullable'])
            ],
        ], [
            'added_column' => new Field(Field::STRING, ['length' => 255, 'nullable']),
        ], [
            'removed_column' => new Field(Field::STRING, ['length' => 255, 'nullable']),
        ]);

        $this->assertStringEndsWith('update_users_table_1.php', $path);
        $this->assertStringStartsWith(database_path('migrations'), $path);

        $this->assertFileEquals(__DIR__ . '/MigrationCreatorTest.update_creates_an_update_migration.txt', $path);
    }

    /** @test */
    function two_updates_creates_a_second_update_migration()
    {
        $this->creator->updateMigration('users', [], [], []);

        $path = $this->creator->updateMigration('users', [
            'updated_column' => [
                new Field(Field::STRING, ['length' => 128, 'nullable']),
                new Field(Field::STRING, ['length' => 255, 'nullable'])
            ],
        ], [
            'added_column' => new Field(Field::STRING, ['length' => 255, 'nullable']),
        ], [
            'removed_column' => new Field(Field::STRING, ['length' => 255, 'nullable']),
        ]);

        $this->assertStringEndsWith('update_users_table_2.php', $path);
        $this->assertStringStartsWith(database_path('migrations'), $path);

        $this->assertFileEquals(__DIR__ . '/MigrationCreatorTest.two_updates_creates_a_second_update_migration.txt', $path);
    }
}
