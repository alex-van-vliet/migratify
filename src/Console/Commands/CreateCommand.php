<?php

namespace AlexVanVliet\Migratify\Console\Commands;

use AlexVanVliet\Migratify\Model;
use AlexVanVliet\Migratify\ModelNotFoundException;
use Illuminate\Console\Command;
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
     * @return void
     */
    public function __construct()
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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $models = config('migratify.models');
        foreach ($models as $model) {
            $reflectionClass = new ReflectionClass($model);
            $attribute = $this->getModelAttribute($reflectionClass);
            $this->info("Attribute found for '$model'.");
            foreach ($attribute->getFields() as $name => $type) {
                $typename = get_class($type);
                $this->line("\tField '$name' has type '$typename'.");
            }
        }
        return 0;
    }
}
