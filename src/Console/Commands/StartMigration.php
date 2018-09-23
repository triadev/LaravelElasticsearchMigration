<?php
namespace Triadev\EsMigration\Console\Commands;

use Illuminate\Console\Command;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;

class StartMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'triadev:elasticsearch:migration:start
                            {versions : versions of migrations}
                            {--source=file : source of migrations (file|database)}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start migration for elasticsearch.';
    
    /**
     * Execute the console command.
     *
     * @param ElasticsearchMigrationContract $elasticsearchMigration
     *
     * @throws \Throwable
     * @throws \Triadev\EsMigration\Exception\MigrationAlreadyDone
     */
    public function handle(ElasticsearchMigrationContract $elasticsearchMigration)
    {
        $versions = explode(',', $this->argument('versions'));
        $source = $this->option('source');

        foreach ($versions as $version) {
            $elasticsearchMigration->migrate(trim($version), $source);
        }
    }
}
