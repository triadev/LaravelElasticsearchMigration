<?php
namespace Tests\Integration\Business\Validation;

use Tests\TestCase;
use Triadev\EsMigration\Exception\FieldDatatypeMigrationFailed;

class FieldDatatypeMigrationTest extends TestCase
{
    /**
     * @test
     * @expectedException \Triadev\EsMigration\Exception\FieldDatatypeMigrationFailed
     */
    public function it_validates_field_datatype_migrations()
    {
        $esMapping = [
            'phpunit' => [
                'properties' => [
                    'text' => [
                        'type' => 'text'
                    ],
                    'keyword' => [
                        'type' => 'keyword'
                    ],
                    'date' => [
                        'type' => 'date'
                    ],
                    'boolean' => [
                        'type' => 'boolean'
                    ],
                    'binary' => [
                        'type' => 'binary'
                    ],
                    'range' => [
                        'type' => 'integer_range'
                    ],
                    'object' => [
                        'type' => 'object'
                    ],
                    'nested' => [
                        'type' => 'nested'
                    ],
                    'geo_point' => [
                        'type' => 'geo_point'
                    ],
                    'geo_shape' => [
                        'type' => 'geo_shape'
                    ],
                    'ip' => [
                        'type' => 'ip'
                    ],
                    'completion' => [
                        'type' => 'completion'
                    ],
                    'token_count' => [
                        'type' => 'token_count'
                    ],
                    'murmur3' => [
                        'type' => 'murmur3'
                    ],
                    'attachment' => [
                        'type' => 'attachment'
                    ],
                    'percolator' => [
                        'type' => 'percolator'
                    ],
                    'valid' => [
                        'type' => 'text'
                    ]
                ]
            ]
        ];
        
        $migrationMapping = [
            'phpunit' => [
                'properties' => [
                    'text' => [
                        'type' => 'long'
                    ],
                    'keyword' => [
                        'type' => 'long'
                    ],
                    'date' => [
                        'type' => 'long'
                    ],
                    'boolean' => [
                        'type' => 'long'
                    ],
                    'binary' => [
                        'type' => 'long'
                    ],
                    'range' => [
                        'type' => 'long'
                    ],
                    'object' => [
                        'type' => 'long'
                    ],
                    'nested' => [
                        'type' => 'long'
                    ],
                    'geo_point' => [
                        'type' => 'long'
                    ],
                    'geo_shape' => [
                        'type' => 'long'
                    ],
                    'ip' => [
                        'type' => 'long'
                    ],
                    'completion' => [
                        'type' => 'long'
                    ],
                    'token_count' => [
                        'type' => 'long'
                    ],
                    'murmur3' => [
                        'type' => 'long'
                    ],
                    'attachment' => [
                        'type' => 'long'
                    ],
                    'percolator' => [
                        'type' => 'long'
                    ],
                    'valid' => [
                        'type' => 'keyword'
                    ]
                ]
            ]
        ];
        
        try {
            (new \Triadev\EsMigration\Business\Validation\FieldDatatypeMigration())->validate(
                $esMapping,
                $migrationMapping
            );
        } catch (FieldDatatypeMigrationFailed $e) {
            $this->assertCount(16, json_decode($e->getMessage()));
            
            throw $e;
        }
    }
}
