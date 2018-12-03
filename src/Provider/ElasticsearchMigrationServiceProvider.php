<?php
namespace Triadev\EsMigration\Provider;

use Illuminate\Support\ServiceProvider;
use Triadev\EsMigration\Console\Commands\ImportFileMigrations;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\ElasticsearchMigration;

class ElasticsearchMigrationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $source = realpath(__DIR__ . '/../Config/config.php');
    
        $this->publishes([
            __DIR__ . '/../Config/config.php' => config_path('triadev-elasticsearch-migration.php'),
        ], 'config');
    
        $this->mergeConfigFrom($source, 'triadev-elasticsearch-migration');
        
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        
        $this->commands([
            ImportFileMigrations::class
        ]);
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigration::class
                );
            }
        );
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStepContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigrationStep::class
                );
            }
        );
    }
    
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ElasticsearchMigrationContract::class, function () {
            return app()->make(ElasticsearchMigration::class);
        });
    }
}
