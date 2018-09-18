<?php
namespace Triadev\EsMigration\Business\Validation;

use Triadev\EsMigration\Exception\FieldDatatypeMigrationFailed;

class FieldDatatypeMigration
{
    /** @var array */
    private $string = ['text', 'keyword'];
    
    /** @var array */
    private $numeric = ['long', 'integer', 'short', 'byte', 'double', 'float', 'half_float', 'scaled_float'];
    
    /** @var array */
    private $date = ['date'];
    
    /** @var array */
    private $boolean = ['boolean'];
    
    /** @var array */
    private $binary = ['binary'];
    
    /** @var array */
    private $range = ['integer_range', 'float_range', 'long_range', 'double_range', 'date_range'];
    
    /** @var array */
    private $validMigrations = [
        'string' => [],
        'numeric' => ['string'],
        'date' => ['string'],
        'boolean' => ['string'],
        'binary' => [],
        'range' => [],
        'object' => [],
        'nested' => [],
        'geo_point' => [],
        'geo_shape' => [],
        'ip' => [],
        'completion' => [],
        'token_count' => [],
        'murmur3' => [],
        'attachment' => [],
        'percolator' => []
    ];
    
    /**
     * Validate
     *
     * @param array $esMapping
     * @param array $migrationMapping
     *
     * @throws FieldDatatypeMigrationFailed
     */
    public function validate(array $esMapping, array $migrationMapping)
    {
        $tmpEsMapping = array_dot($esMapping);
        $tmpMigrationMapping = array_dot($migrationMapping);
        
        $failedFields = [];
        
        foreach ($tmpEsMapping as $key => $value) {
            if (preg_match('/^.*type$/', $key) && array_key_exists($key, $tmpMigrationMapping)) {
                $esMappingDatatype = $this->getFieldDatatype($tmpEsMapping[$key]);
                $migrationMappingDatatype = $this->getFieldDatatype($tmpMigrationMapping[$key]);
                
                if ($esMappingDatatype == $migrationMappingDatatype) {
                    continue;
                }
                
                if (!in_array($migrationMappingDatatype, $this->validMigrations[$esMappingDatatype])) {
                    $failedFields[] = $key;
                    continue;
                }
            }
        }
        
        if (!empty($failedFields)) {
            throw new FieldDatatypeMigrationFailed(json_encode($failedFields));
        }
    }
    
    private function getFieldDatatype(string $datatype) : ?string
    {
        $datatypes = [
            'string' => $this->string,
            'numeric' => $this->numeric,
            'date' => $this->date,
            'boolean' => $this->boolean,
            'binary' => $this->binary,
            'range' => $this->range,
            'object' => ['object'],
            'nested' => ['nested'],
            'geo_point' => ['geo_point'],
            'geo_shape' => ['geo_shape'],
            'ip' => ['ip'],
            'completion' => ['completion'],
            'token_count' => ['token_count'],
            'murmur3' => ['murmur3'],
            'attachment' => ['attachment'],
            'percolator' => ['percolator']
        ];
        
        foreach ($datatypes as $key => $d) {
            if (in_array($datatype, $d)) {
                return $key;
            }
        }
        
        return null;
    }
}
