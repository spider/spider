<?php
namespace Michaels\Spider\Test\Stubs;

use Michaels\Spider\Connections\Manager;
use Michaels\Spider\Drivers\DriverInterface;
use Michaels\Spider\Queries\QueryInterface;

class DriverStub implements DriverInterface
{

    /**
     * Connect to the database
     *
     * @param Manager|array $properties credentials
     *
     * @return $this
     */
    public function connect($properties)
    {
        // TODO: Implement connect() method.
    }

    /**
     * List available databases
     * @return array
     */
    public function listDbs()
    {
        // TODO: Implement listDbs() method.
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
        // TODO: Implement openDb() method.
    }

    /**
     * Close the database
     * @return $this
     */
    public function closeDb()
    {
        // TODO: Implement closeDb() method.
    }

    /**
     * Create a new Vertex (or node)
     *
     * @param array $properties
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Record Created
     */
    public function addVertex($properties)
    {
        // TODO: Implement addVertex() method.
    }

    /**
     * Create a new edge (relationship)
     *
     * @param $from
     * @param $to
     * @param $properties
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Edge Created
     */
    public function addEdge($from, $to, $properties)
    {
        // TODO: Implement addEdge() method.
    }

    /**
     * Retrieve a vertex
     *
     * @param int|string $id
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Edge Created
     */
    public function getVertex($id)
    {
        // TODO: Implement getVertex() method.
    }

    /**
     * Retrieve an Edge
     *
     * @param string|int $id
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Edge record
     */
    public function getEdge($id)
    {
        // TODO: Implement getEdge() method.
    }

    /**
     * @param string|int $id
     * @param array      $properties
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Vertex record
     */
    public function updateVertex($id, $properties)
    {
        // TODO: Implement updateVertex() method.
    }

    /**
     * Update an edge
     *
     * @param string|int $id
     * @param array      $properties
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Edge record
     */
    public function updateEdge($id, $properties)
    {
        // TODO: Implement updateEdge() method.
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
        // TODO: Implement dropVertex() method.
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
        // TODO: Implement dropEdge() method.
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
        // TODO: Implement command() method.
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
        // TODO: Implement query() method.
}}
