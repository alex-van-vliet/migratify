<?php


namespace AlexVanVliet\Migratify\Console\Commands;


use AlexVanVliet\Migratify\Model;
use Illuminate\Database\Migrations\MigrationCreator as MigrationCreatorBase;

class MigrationCreator extends MigrationCreatorBase
{
    public function stubPath()
    {
        return __DIR__.'/stubs';
    }

    protected function getStub($table, $create)
    {
        if ($create) {
            $stub = $this->files->exists($customPath = $this->customStubPath.'/migration.create.stub')
                ? $customPath
                : $this->stubPath().'/migration.create.stub';
        } else {
            $stub = $this->files->exists($customPath = $this->customStubPath.'/migration.update.stub')
                ? $customPath
                : $this->stubPath().'/migration.update.stub';
        }

        return $this->files->get($stub);
    }

    protected function populateCreate(string $stub, Model $attribute)
    {
        $up = [];
        foreach ($attribute->getFields() as $name => $field)
        {
            $lines = $field->create($name);
            if (is_array($lines))
                $up += $lines;
            else
                $up[] = $lines;
        }
        $up = array_map(fn($line) => "            $line", $up);
        $up = implode("\n", $up);

        $stub = str_replace(
            ['{{ up }}', '{{up}}'],
            $up, $stub
        );

        return $stub;
    }

    public function createMigration(string $table, Model $attribute)
    {
        $name = "create_${table}_table";
        $path = database_path('migrations');
        $create = true;

        $this->ensureMigrationDoesntAlreadyExist($name, $path);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        $path = $this->getPath($name, $path);

        $this->files->ensureDirectoryExists(dirname($path));

        $stub = $this->populateStub($name, $stub, $table);

        $stub = $this->populateCreate($stub, $attribute);

        $this->files->put($path, $stub);

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table);

        return $path;
    }
}
