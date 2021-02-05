<?php


namespace AlexVanVliet\Migratify\Providers;

use AlexVanVliet\Migratify\Console\Commands\CreateCommand;
use AlexVanVliet\Migratify\Console\Commands\MigrationCreator;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class MigratifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CreateCommand::class, fn(Application $app) => new CreateCommand($app->make('migrator')));
        $this->app->singleton(MigrationCreator::class, fn(Application $app) => new MigrationCreator($app->make('files'), $app->basePath('stubs')));
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/migratify.php' => config_path('migratify.php'),
        ]);
        $this->mergeConfigFrom(__DIR__ . '/../config/migratify.php', 'migratify');
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateCommand::class,
            ]);
        }
    }
}
