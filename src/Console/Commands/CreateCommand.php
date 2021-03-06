<?php

namespace AlexVanVliet\Migratify\Console\Commands;

use AlexVanVliet\Migratify\Database\BlueprintMock;
use AlexVanVliet\Migratify\Database\DatabaseManagerMock;
use AlexVanVliet\Migratify\Fields\Field;
use AlexVanVliet\Migratify\Model;
use AlexVanVliet\Migratify\ModelNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\Migrator;

class CreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migratify:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the new migrations';

    /**
     * Create a new command instance.
     *
     * @param Migrator $migrator The migrator.
     *
     * @return void
     */
    public function __construct(
        protected Migrator $migrator,
    )
    {
        parent::__construct();
    }

    /**
     * Get the state of the database.
     *
     * @return BlueprintMock[]
     * @throws BindingResolutionException
     */
    public function getState(Application $application): array
    {
        $db = $application->make('db');

        $mockedDatabaseManager = new DatabaseManagerMock();
        $application->singleton('db', fn() => $mockedDatabaseManager);

        $files = $this->migrator->getMigrationFiles(database_path('migrations'));
        $this->migrator->requireFiles($files);

        foreach ($files as $file) {
            $migration = $this->migrator->resolve($this->migrator->getMigrationName($file));
            $migration->up();
        }
        $application->singleton('db', fn() => $db);

        return $mockedDatabaseManager->connection()->getSchemaBuilder()->getBlueprints();
    }

    /**
     * Get the difference between two blueprints.
     *
     * @param BlueprintMock $current The blueprint found in the migrations.
     * @param BlueprintMock $expected The blueprint wanted in the model.
     * @return Field[][] The added, removed and changed fields.
     */
    public function getDiff(BlueprintMock $current, BlueprintMock $expected): array
    {
        $additions = [];
        $updates = [];
        foreach ($expected->getFields() as $name => $field) {
            if (array_key_exists($name, $current->getFields())) {
                if (!$field->equals($current->getFields()[$name])) {
                    $updates[$name] = [$current->getFields()[$name], $field];
                }
            } else {
                $additions[$name] = $field;
            }
        }

        $removals = [];
        foreach ($current->getFields() as $name => $field) {
            if (!array_key_exists($name, $expected->getFields())) {
                $removals[$name] = $field;
            }
        }

        return [
            'additions' => $additions,
            'removals' => $removals,
            'updates' => $updates,
        ];
    }

    /**
     * Execute the console command.
     *
     * @param Application $application The application.
     * @param MigrationCreator $creator The migration creator.
     * @return int
     * @throws ModelNotFoundException
     * @throws BindingResolutionException
     * @throws FileNotFoundException
     */
    public function handle(Application $application, MigrationCreator $creator): int
    {
        $state = $this->getState($application);
        $models = config('migratify.models');
        foreach ($models as $model) {
            $attribute = Model::from_attribute($model);
            $table = (new $model())->getTable();
            $this->info("Attribute found for '$model'.");
            foreach ($attribute->getFields() as $name => $type) {
                $typename = get_class($type);
                $this->line("\tField '$name' has type '$typename'.");
            }
            $stateForModel = $state[$table] ?? null;
            if ($stateForModel === null) {
                $creator->createMigration($table, $attribute->toBlueprint($table)->getFields());
            } else {
                $diff = $this->getDiff($stateForModel, $attribute->toBlueprint($table));
                if (!empty($diff['additions']) or !empty($diff['removals']) or !empty($diff['updates'])) {
                    if (!empty($diff['updates']) or !empty($diff['removals'])) {
                        $this->warn("Update migration detected, down currently not supported for updates and removals.");
                    }
                    $creator->updateMigration($table, ...$diff);
                }
            }
        }
        return 0;
    }
}
