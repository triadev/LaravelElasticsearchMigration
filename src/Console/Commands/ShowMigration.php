<?php
namespace Triadev\EsMigration\Console\Commands;

use Illuminate\Console\Command;
use Triadev\EsMigration\Contract\Repository\ElasticsearchMigrationContract;

class ShowMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'triadev:elasticsearch:migration:show
                            {sortField=created_at : database field for sort}
                            {sortOrder=desc : sort direction}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Show migrations.';
    
    /**
     * Execute the console command.
     *
     * @param ElasticsearchMigrationContract $elasticsearchMigrationRepository
     */
    public function handle(ElasticsearchMigrationContract $elasticsearchMigrationRepository)
    {
        $sortField = $this->argument('sortField');
        
        $migrations = $elasticsearchMigrationRepository->all(['migration', 'status', 'created_at', 'updated_at']);
        
        switch ($this->argument('sortOrder')) {
            case 'asc':
                $migrations = $migrations->sortBy($sortField);
                break;
            case 'desc':
                $migrations = $migrations->sortByDesc($sortField);
                break;
            default:
                break;
        }
        
        $this->table(
            ['Migration', 'Status', 'Created', 'Updated'],
            $migrations->toArray()
        );
    }
}
