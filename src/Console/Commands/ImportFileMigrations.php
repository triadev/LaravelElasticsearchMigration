<?php
namespace Triadev\EsMigration\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Triadev\EsMigration\Business\Mapper\MigrationTypes;
use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;

class ImportFileMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'triadev:es-migration:import-file-migrations {migration} {filePath}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import file migrations.';
    
    /** @var ElasticsearchMigrationContract */
    private $elasticsearchMigrationService;
    
    /**
     * ImportFileMigrations constructor.
     * @param ElasticsearchMigrationContract $elasticsearchMigrationService
     */
    public function __construct(ElasticsearchMigrationContract $elasticsearchMigrationService)
    {
        parent::__construct();
        
        $this->elasticsearchMigrationService = $elasticsearchMigrationService;
    }
    
    /**
     * Execute the console command.
     *
     * @throws \Exception
     */
    public function handle()
    {
        $migration = (string)$this->argument('migration');
        $filePathSelector = (string)$this->argument('filePath');
        
        $filePath = config('triadev-elasticsearch-migration.filePath');
        if (!array_has($filePath, $filePathSelector)) {
            throw new \Exception("No migration file path was defined.");
        }
        
        $migrationSteps = $this->getMigrationSteps($migration, $filePath[$filePathSelector]);
        if (!empty($migrationSteps)) {
            $this->elasticsearchMigrationService->createMigration($migration);
            
            $this->importMigrationSteps(
                $migration,
                $migrationSteps
            );
        }
    }
    
    /**
     * @param string $migration
     * @param string $filePath
     * @return array
     * @throws \Exception
     */
    private function getMigrationSteps(string $migration, string $filePath) : array
    {
        $filePath = $filePath . DIRECTORY_SEPARATOR . $migration;
        if (!is_dir($filePath)) {
            throw new \Exception("The migration directory does not exist.");
        }
        
        $files = [];
    
        foreach (scandir($filePath) as $key => $value) {
            if (!in_array($value, ['.', '..'])) {
                $files[] = $filePath . DIRECTORY_SEPARATOR . $value;
            }
        }
        
        return $files;
    }
    
    /**
     * @param string $migration
     * @param array $migrationSteps
     */
    private function importMigrationSteps(string $migration, array $migrationSteps)
    {
        try {
            foreach ($this->getValidMigrationSteps($migrationSteps) as $validMigrationStep) {
                $this->elasticsearchMigrationService->addMigrationStep(
                    $migration,
                    /** @scrutinizer ignore-type */ array_get($validMigrationStep, 'type'),
                    /** @scrutinizer ignore-type */ array_get($validMigrationStep, 'params'),
                    array_get($validMigrationStep, 'priority', 1),
                    array_get($validMigrationStep, 'stopOnFailure', true)
                );
            }
        } catch (\Exception $e) {
            Log::error("The migration steps could not be imported.");
        }
    }
    
    /**
     * @param array $migrationSteps
     * @return array
     * @throws \Exception
     */
    private function getValidMigrationSteps(array $migrationSteps) : array
    {
        $migrationTypes = new MigrationTypes();
        
        $validMigrationSteps = [];
    
        foreach ($migrationSteps as $migrationStep) {
            $step = require $migrationStep;
            
            if (Validator::make($step, [
                'type' => 'required|string',
                'params' => 'required|array',
                'priority' => 'integer',
                'stopOnFailure' => 'boolean'
            ])->fails()) {
                throw new \Exception("The migration step is invalid.");
            }
        
            if (!$migrationTypes->isMigrationTypeValid(
                /** @scrutinizer ignore-type */ array_get($step, 'type')
            )) {
                throw new \Exception("The migration step type is invalid.");
            }
        
            $validMigrationSteps[] = $step;
        }
        
        return $validMigrationSteps;
    }
}
