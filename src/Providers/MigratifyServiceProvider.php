<?php


namespace AlexVanVliet\Migratify\Providers;

use AlexVanVliet\Migratify\Console\Commands\CreateCommand;
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
        //
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
