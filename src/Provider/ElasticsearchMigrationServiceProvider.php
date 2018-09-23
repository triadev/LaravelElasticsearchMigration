<?php
namespace Triadev\EsMigration\Provider;

use Illuminate\Support\ServiceProvider;
use Triadev\EsMigration\Console\Commands\ShowMigration;
use Triadev\EsMigration\Console\Commands\StartMigration;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;
use Triadev\EsMigration\Contract\ElasticsearchMigrationDatabaseContract;
use Triadev\EsMigration\ElasticsearchMigration;
use Triadev\EsMigration\ElasticsearchMigrationDatabase;

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
    
        if ($this->app->runningInConsole()) {
            $this->commands([
                StartMigration::class,
                ShowMigration::class
            ]);
        }
        
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationStatusContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigrationStatus::class
                );
            }
        );
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigration::class
                );
            }
        );
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigrations::class
                );
            }
        );
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsCreateIndexContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigrationsCreateIndex::class
                );
            }
        );
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateIndexContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigrationsUpdateIndex::class
                );
            }
        );
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsAliasContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigrationsAlias::class
                );
            }
        );
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsDeleteByQueryContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigrationsDeleteByQuery::class
                );
            }
        );
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsUpdateByQueryContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigrationsUpdateByQuery::class
                );
            }
        );
    
        $this->app->bind(
            \Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationsReindexContract::class,
            function () {
                return app()->make(
                    \Triadev\EsMigration\Business\Repository\ElasticsearchMigrationsReindex::class
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
    
        $this->app->singleton(ElasticsearchMigrationDatabaseContract::class, function () {
            return app()->make(ElasticsearchMigrationDatabase::class);
        });
    }
}
