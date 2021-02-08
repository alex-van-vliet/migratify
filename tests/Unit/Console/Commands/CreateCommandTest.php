<?php


namespace AlexVanVliet\Migratify\Tests\Unit\Console\Commands;


use AlexVanVliet\Migratify\Console\Commands\CreateCommand;
use AlexVanVliet\Migratify\Console\Commands\MigrationCreator;
use AlexVanVliet\Migratify\Fields\Field;
use AlexVanVliet\Migratify\Model;
use AlexVanVliet\Migratify\Tests\TestCase;
use Illuminate\Database\Eloquent\Model as BaseModel;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\File;

#[Model([
    'id' => [Field::ID],
    'column' => [Field::STRING, ['length' => 128]],
    'password' => [Field::STRING, [], ['guarded']],
], [
    'timestamps' => false,
])]
class CreateCommand_FakeModel extends BaseModel
{
}

class CreateCommandTest extends TestCase
{
    protected CreateCommand $command;
    protected MigrationCreator $creator;

    public function setUp(): void
    {
        parent::setUp();

        $this->command = app()->make(CreateCommand::class);
        $this->creator = new MigrationCreator(app()->make('files'), app()->basePath('stubs'));

        File::deleteDirectory(database_path('migrations'), true);
    }

    /** @test */
    function diff_detects_new_fields()
    {
        $model = new Model([
            'id' => [Field::ID],
            'password' => [Field::STRING, [], ['guarded']],
        ], [
            'timestamps' => false,
        ]);

        $diff = $this->command->getDiff($model->toBlueprint('table'),
            Model::from_attribute(CreateCommand_FakeModel::class)->toBlueprint('table'));
        $this->assertEquals([
            'additions' => ['column' => new Field(Field::STRING, ['length' => 128])],
            'removals' => [],
            'updates' => [],
        ], $diff);
    }

    /** @test */
    function diff_detects_removed_fields()
    {
        $model = new Model([
            'id' => [Field::ID],
            'column' => [Field::STRING, ['length' => 128]],
            'password' => [Field::STRING, [], ['guarded']],
            'old' => [Field::TEXT],
        ], [
            'timestamps' => false,
        ]);

        $diff = $this->command->getDiff($model->toBlueprint('table'),
            Model::from_attribute(CreateCommand_FakeModel::class)->toBlueprint('table'));
        $this->assertEquals([
            'additions' => [],
            'removals' => ['old' => new Field(Field::TEXT)],
            'updates' => [],
        ], $diff);
    }

    /** @test */
    function the_current_state_can_be_detected()
    {
        $this->creator->createMigration('table', Model::from_attribute(CreateCommand_FakeModel::class)->toBlueprint('table')->getFields());

        $state = $this->command->getState(app());

        $this->assertEquals([
            'table' => Model::from_attribute(CreateCommand_FakeModel::class)->toBlueprint('table'),
        ], $state);
    }
}
