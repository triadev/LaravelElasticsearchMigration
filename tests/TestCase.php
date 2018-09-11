<?php

namespace Tests;

use Triadev\EsMigration\Provider\ElasticsearchMigrationServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
    }
    
    /**
     * @inheritDoc
     *
     * (Increase visibility to public)
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }
    
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('triadev-elasticsearch-migration', [
            'host' => 'localhost',
            'port' => 9200,
            'scheme' => 'http',
            'user' => '',
            'pass' => '',
            'migration' => [
                'filePath' => __DIR__ . '/Resources/Migrations'
            ]
        ]);
    }

    /**
     * Get package providers.  At a minimum this is the package being tested, but also
     * would include packages upon which our package depends, e.g. Cartalyst/Sentry
     * In a normal app environment these would be added to the 'providers' array in
     * the config/app.php file.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            ElasticsearchMigrationServiceProvider::class
        ];
    }
}
