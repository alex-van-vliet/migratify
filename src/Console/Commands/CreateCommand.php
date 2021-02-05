<?php

namespace AlexVanVliet\Migratify\Console\Commands;

use AlexVanVliet\Migratify\Database\BlueprintMock;
use AlexVanVliet\Migratify\Database\DatabaseManagerMock;
use AlexVanVliet\Migratify\Model;
use AlexVanVliet\Migratify\ModelNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\Migrator;
use ReflectionClass;

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
     * Get the model attribute.
     * @param $reflectionClass
     * @return Model
     */
    protected function getModelAttribute(ReflectionClass $reflectionClass)
    {
        $attributes = $reflectionClass->getAttributes();
        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof Model)
                return $instance;
        }

        throw new ModelNotFoundException($reflectionClass->getName());
    }

    /**
     * Get the state of the database.
     */
    protected function getState(Application $application)
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

    public function getDiff(BlueprintMock $current, BlueprintMock $expected)
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

        if (count($updates) != 0 or count($removals) != 0) {
            $this->warn("Update migration detected, down currently not supported for updates and removals.");
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
     * @return int
     */
    public function handle(Application $application, MigrationCreator $creator)
    {
        $state = $this->getState($application);
        $models = config('migratify.models');
        foreach ($models as $model) {
            $reflectionClass = new ReflectionClass($model);
            $attribute = $this->getModelAttribute($reflectionClass);
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
                if (empty($diff['additions']) and empty($diff['removals']) and empty($diff['updates'])) {
                } else {
                    $creator->updateMigration($table, ...$diff);
                }
            }
        }
        return 0;
    }
}
