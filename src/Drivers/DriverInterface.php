<?php
namespace Michaels\Spider\Drivers;

use Michaels\Spider\Queries\QueryInterface;

/**
 * Class OrientDriver
 * @package Michaels\Spider\Drivers\OrientDB
 */
/* ToDo: Transaction Support */

interface DriverInterface
{
    /**
     * Connect to the database
     *
     * @param array $properties credentials
     *
*@return $this
     */
    public function connect(array $properties);

    /**
     * List available databases
     * @return array
     */
    public function listDbs();

    /**
     * Opens a specific database
     * @param string $database
     *
     * @return $this
     */
    public function openDb($database);

    /**
     * Close the database
     * @return $this
     */
    public function closeDb();

    /**
     * Create a new Vertex (or node)
     * @param array $properties
     *
     * @return mixed Record Created
     */
    public function addVertex($properties);

    /**
     * Create a new edge (relationship)
     *
     * @param $from
     * @param $to
     * @param $properties
     *
     * @return mixed Edge Created
     */
    public function addEdge($from, $to, $properties);

    /**
     * Retrieve a vertex
     * @param int|string $id
     *
     * @return mixed Edge Created
     */
    public function getVertex($id);

    /**
     * Retrieve an Edge
     * @param string|int $id
     *
     * @return mixed Edge record
     */
    public function getEdge($id);

    /**
     * @param string|int $id
     * @param array      $properties
     *
     * @return mixed Vertex record
     */
    public function updateVertex($id, $properties);

    /**
     * Update an edge
     *
     * @param string|int $id
     * @param array      $properties
     *
     * @return mixed Edge record
     */
    public function updateEdge($id, $properties);

    /**
     * Delete a Vertex (node)
     * @param string|int $id
     *
     * @return $this
     */
    public function dropVertex($id);

    /**
     * Delete an Edge (relationship)
     * @param $id
     *
     * @return $this
     */
    public function dropEdge($id);

    /**
     * Execute a command in the graph database's native language (orient, sparql, cypher, etc)
     *
     * @param string $command
     *
     * @return mixed
     */
    public function command($command);

    /**
     * Execute a query
     * From the Queryies\QueryBuilder which translates to a native or gremlin statement
     *
     * @param QueryInterface $query
     *
     * @return mixed
     */
    public function query(QueryInterface $query);

    /**
     * Map a raw result to the Spider Response
     * @param $results
     * @return Graph
     */
    public function mapToSpiderResponse($results);
}
