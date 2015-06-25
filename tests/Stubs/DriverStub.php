<?php
namespace Michaels\Spider\Test\Stubs;

use Michaels\Spider\Connections\Manager;
use Michaels\Spider\Drivers\DriverInterface;
use Michaels\Spider\Graphs\Graph;
use Michaels\Spider\Queries\QueryInterface;

class DriverStub implements DriverInterface
{

    protected function returnData()
    {
        return new NativeReturnStub([
            'one' => 1,
            'two' => true,
            'three' => 'three',
        ]);
    }

    /**
     * Connect to the database
     *
     * @param Manager|array $properties credentials
     *
     * @return $this
     */
    public function connect(array $properties)
    {
        return $this;
    }

    /**
     * List available databases
     * @return array
     */
    public function listDbs()
    {
        return ['dbOne', 'dbTwo'];
    }

    /**
     * Opens a specific database
     *
     * @param string $database
     *
     * @return $this
     */
    public function openDb($database)
    {
        return $this;
    }

    /**
     * Close the database
     * @return $this
     */
    public function closeDb()
    {
        return $this;
    }

    /**
     * Create a new Vertex (or node)
     *
     * @param array $properties
     *
     * @return mixed Record Created
     */
    public function addVertex($properties)
    {
        return $this->returnData();
    }

    /**
     * Create a new edge (relationship)
     *
     * @param $from
     * @param $to
     * @param $properties
     *
     * @return mixed Edge Created
     */
    public function addEdge($from, $to, $properties)
    {
        return $this->returnData();
    }

    /**
     * Retrieve a vertex
     *
     * @param int|string $id
     *
     * @return mixed Edge Created
     */
    public function getVertex($id)
    {
        return $this->returnData();
    }

    /**
     * Retrieve an Edge
     *
     * @param string|int $id
     *
     * @return mixed Edge record
     */
    public function getEdge($id)
    {
        return $this->returnData();
    }

    /**
     * @param string|int $id
     * @param array      $properties
     *
     * @return mixed Vertex record
     */
    public function updateVertex($id, $properties)
    {
        return $this->returnData();
    }

    /**
     * Update an edge
     *
     * @param string|int $id
     * @param array      $properties
     *
     * @return mixed Edge record
     */
    public function updateEdge($id, $properties)
    {
        return $this->returnData();
    }

    /**
     * Delete a Vertex (node)
     *
     * @param string|int $id
     *
     * @return $this
     */
    public function dropVertex($id)
    {
        return $this;
    }

    /**
     * Delete an Edge (relationship)
     *
     * @param $id
     *
     * @return $this
     */
    public function dropEdge($id)
    {
        return $this;
    }

    /**
     * Execute a command in the graph database's native language (orient, sparql, cypher, etc)
     *
     * @param string $command
     *
     * @return mixed
     */
    public function command($command)
    {
        return $this->returnData();
    }

    /**
     * Execute a query
     * From the Queryies\QueryBuilder which translates to a native or gremlin statement
     *
     * @param QueryInterface $query
     *
     * @return mixed
     */
    public function query(QueryInterface $query)
    {
        return $this->returnData();
    }

    /**
     * Map a raw result to the Spider Response
     * @param $results
     * @return \Michaels\Spider\Graphs\Graph
     */
    public function mapToSpiderResponse($results)
    {
        return new Graph($results);
    }
}
