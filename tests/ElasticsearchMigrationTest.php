<?php
namespace Tests;

use Triadev\EsMigration\Contract\ElasticsearchMigrationContract;

class ElasticsearchMigrationTest extends TestCase
{
    /** @var ElasticsearchMigrationContract */
    private $service;
    
    public function setUp()
    {
        parent::setUp();
        
        $this->service = app(ElasticsearchMigrationContract::class);
    }
    
    /**
     * @test
     */
    public function it()
    {
        //
    }
}
