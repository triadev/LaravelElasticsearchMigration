<?php
namespace Triadev\EsMigration\Business\Repository;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

class ElasticsearchClients
{
    /**
     * @var array
     */
    private $elasticsearchClients = [];
    
    /** @var ClientBuilder */
    private $clientBuilder;
    
    /**
     * ElasticsearchClients constructor.
     */
    public function __construct()
    {
        $this->clientBuilder = ClientBuilder::create();
    }
    
    /**
     * Add
     *
     * @param string $esClientKey
     * @param string $host
     * @param int $port
     * @param string $scheme
     * @param null|string $user
     * @param null|string $password
     * @param int $retries
     */
    public function add(
        string $esClientKey,
        string $host,
        int $port,
        string $scheme,
        ?string $user = null,
        ?string $password = null,
        int $retries = 1
    ) {
        $this->elasticsearchClients[$esClientKey] = $this->clientBuilder->setHosts([
            [
                'host' => $host,
                'port' => $port,
                'scheme' => $scheme,
                'user' => $user,
                'pass' => $password
            ]
        ])->setRetries($retries)->build();
    }
    
    /**
     * Get
     *
     * @param string $esClientKey
     * @return Client|null
     */
    public function get(string $esClientKey) : ?Client
    {
        return array_get($this->elasticsearchClients, $esClientKey);
    }
    
    /**
     * All
     *
     * @return Client[]
     */
    public function all() : array
    {
        return $this->elasticsearchClients;
    }
}
