<?php


namespace AlexVanVliet\Migratify\Console\Commands;


use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Migrations\MigrationCreator as MigrationCreatorBase;
use InvalidArgumentException;

/**
 * Add or merge an array with lines.
 *
 * @param array $arr The array
 * @param string|array $lines Either one line as a string or several lines as an array
 * @return array
 */
function addOrMerge(array $arr, string|array $lines): array
{
    if (is_array($lines))
        return $arr + $lines;
    $arr[] = $lines;
    return $arr;
}

class MigrationCreator extends MigrationCreatorBase
{
    /**
     * Get the path to the stubs.
     *
     * @return string
     */
    public function stubPath()
    {
        return __DIR__ . '/stubs';
    }

    /**
     * Get the stub for a given table.
     *
     * @param string|null $table The name of the table.
     * @param bool $create Whether it is a create or an update stub.
     * @return string
     * @throws FileNotFoundException
     */
    protected function getStub($table, $create)
    {
        if ($create) {
            $stub = $this->files->exists($customPath = $this->customStubPath . '/migration.create.stub')
                ? $customPath
                : $this->stubPath() . '/migration.create.stub';
        } else {
            $stub = $this->files->exists($customPath = $this->customStubPath . '/migration.update.stub')
                ? $customPath
                : $this->stubPath() . '/migration.update.stub';
        }

        return $this->files->get($stub);
    }

    /**
     * Populate the up in the create stub.
     *
     * @param string $stub The stub.
     * @param array $fields The fields to add.
     * @return string
     */
    protected function populateCreate(string $stub, array $fields): string
    {
        $up = [];
        foreach ($fields as $name => $field) {
            $up = addOrMerge($up, $field->create($name)[0]);
        }
        $up = array_map(fn($line) => "            $line;", $up);
        $up = implode("\n", $up);

        $stub = str_replace(
            ['{{ up }}', '{{up}}'],
            $up, $stub
        );

        return $stub;
    }

    /**
     * Create a create migration.
     *
     * @param string $table The name of the table
     * @param array $fields The fields to add.
     * @return string The path to the migration.
     * @throws FileNotFoundException
     */
    public function createMigration(string $table, array $fields): string
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

        $stub = $this->populateCreate($stub, $fields);

        $this->files->put($path, $stub);

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table);

        return $path;
    }

    /**
     * Populate the up and down in the update stub.
     *
     * @param string $stub The stub.
     * @param array $updates The fields to update.
     * @param array $additions The fields to add.
     * @param array $removals The fields to remove.
     * @return string
     */
    protected function populateUpdate(string $stub, array $updates, array $additions, array $removals): string
    {
        $up = [];
        $down = [];
        foreach ($additions as $name => $field) {
            [$upLines, $downLines] = $field->create($name);
            $up = addOrMerge($up, $upLines);
            $down = addOrMerge($down, $downLines);
        }
        foreach ($removals as $name => $field) {
            [$upLines, $downLines] = $field->remove($name);
            $up = addOrMerge($up, $upLines);
            $down = addOrMerge($down, $downLines);
        }
        foreach ($updates as $name => [$from, $to]) {
            [$upLines, $downLines] = $to->update($name, $from);
            $up = addOrMerge($up, $upLines);
            $down = addOrMerge($down, $downLines);
        }
        $up = array_map(fn($line) => "            $line;", $up);
        $up = implode("\n", $up);
        $down = array_map(fn($line) => "            $line;", $down);
        $down = implode("\n", $down);

        $stub = str_replace(
            ['{{ up }}', '{{up}}'],
            $up, $stub
        );
        $stub = str_replace(
            ['{{ down }}', '{{down}}'],
            $down, $stub
        );

        return $stub;
    }

    /**
     * Create an update migration.
     *
     * @param string $table The name of the table
     * @param array $updates The fields to update.
     * @param array $additions The fields to add.
     * @param array $removals The fields to remove.
     * @return string The path to the migration.
     * @throws FileNotFoundException
     */
    public function updateMigration(string $table, array $updates, array $additions, array $removals): string
    {
        $basename = "update_${table}_table";
        $updateNumber = 1;
        $path = database_path('migrations');
        $create = false;

        do {
            $exists = false;
            try {
                $this->ensureMigrationDoesntAlreadyExist("{$basename}_{$updateNumber}", $path);
            } catch (InvalidArgumentException) {
                $exists = true;
                $updateNumber += 1;
            }
        } while ($exists);

        // First we will get the stub file for the migration, which serves as a type
        // of template for the migration. Once we have those we will populate the
        // various place-holders, save the file, and run the post create event.
        $stub = $this->getStub($table, $create);

        $path = $this->getPath("{$basename}_{$updateNumber}", $path);

        $this->files->ensureDirectoryExists(dirname($path));

        $stub = $this->populateStub("{$basename}_{$updateNumber}", $stub, $table);

        $stub = $this->populateUpdate($stub, $updates, $additions, $removals);

        $this->files->put($path, $stub);

        // Next, we will fire any hooks that are supposed to fire after a migration is
        // created. Once that is done we'll be ready to return the full path to the
        // migration file so it can be used however it's needed by the developer.
        $this->firePostCreateHooks($table);

        return $path;
    }
}
