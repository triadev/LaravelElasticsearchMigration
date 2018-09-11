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
    protected $signature = 'triadev:es:migration:start
                            {version : version of migration}';
    
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
        $elasticsearchMigration->migrate($this->argument('version'));
    }
}
