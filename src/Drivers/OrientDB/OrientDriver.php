<?php
namespace Michaels\Spider\Drivers\OrientDB;

use Michaels\Spider\Connections\Manager;
use Michaels\Spider\Drivers\ConnectionException;
use Michaels\Spider\Drivers\DriverInterface;
use Michaels\Spider\Graphs\Graph;
use Michaels\Spider\Queries\QueryInterface;
use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\ID;
use PhpOrient\Protocols\Binary\Data\Record;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Driver for Native OrientDB (not using gremlin)
 * @package Michaels\Spider\Drivers\OrientDB
 */
class OrientDriver implements DriverInterface
{
    /**
     * Create a new instance with a client
     */
    public function __construct()
    {
        $this->client = new PhpOrient();
    }

    /**
     * Connect to the database
     *
     * @param Manager|array $properties Connection credentials
     *
     * @return $this
     * @throws ConnectionException if connection is refused or broken
     */
    public function connect(array $properties)
    {
        $this->client->configure($properties);
        $this->client->connect();
    }

    /**
     * Create a new database
     * @param string $name
     * @param null   $storageType
     * @param null   $databaseType
     *
     * @return bool
     */
    public function createDb($name, $storageType = null, $databaseType = null)
    {
        return $this->client->dbCreate($name, $storageType, $databaseType);
    }

    /**
     * Delete a database
     * @param string $name
     *
     * @return bool
     */
    public function dropDb($name)
    {
        return $this->client->dbDrop($name);
    }

    /**
     * Verify existence of a database
     * @param string $name
     *
     * @return bool
     */
    public function dbExists($name)
    {
        return $this->client->dbExists($name);
    }

    /**
     * List available databases
     * @return array
     */
    public function listDbs()
    {
        return $this->client->dbList()['databases'];
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
        $this->client->dbOpen($database); // What if I *want* the cluster map?
        return $this;
    }

    /**
     * Close the database
     * @return $this
     */
    public function closeDb()
    {
        $this->client->dbClose(); // returns int
        return $this;
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
        list($properties, $recordClass) = $this->parseOClass($properties);

        $record = $this->buildRecord($properties, $recordClass);
        $result = $this->client->recordCreate($record);

        return $result; // ToDo: Convert to GraphCollection
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
        $from = $this->parseRidToString($from);
        $to = $this->parseRidToString($to);
        list($properties, $class) = $this->parseOClass($properties, 'E');

        $statement = "create edge $class from $from to $to content " . json_encode($properties);

        return $this->client->command($statement); // ToDo: Convert to GraphCollection
    }

    /**
     * Retrieve a vertex
     *
     * @param int|string $rid
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Edge Created
     */
    public function getVertex($rid)
    {
        return $this->getRecord($rid); // ToDo: Convert to GraphCollection
    }

    /**
     * Retrieve an Edge
     *
     * @param string|int $rid
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Edge record
     */
    public function getEdge($rid)
    {
        return $this->getRecord($rid); // ToDo: Convert to GraphCollection
    }

    /**
     * @param string|int $rid
     * @param array      $properties
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Vertex record
     */
    public function updateVertex($rid, $properties)
    {
        return $this->updateRecord($rid, $properties); // ToDo: Convert to GraphCollection
    }

    /**
     * Update an edge
     *
     * @param string|int $rid
     * @param array      $properties
     *
     * @return \Michaels\Spider\Graphs\GraphCollection Edge record
     */
    public function updateEdge($rid, $properties)
    {
        return $this->updateRecord($rid, $properties); // ToDo: Convert to GraphCollection
    }

    /**
     * Delete a Vertex (node)
     *
     * @param string|int $rid
     *
     * @return $this
     */
    public function dropVertex($rid)
    {
        $this->dropRecord($rid); // returns RecordDelete|bool
        return $this;
    }

    /**
     * Delete an Edge (relationship)
     *
     * @param $rid
     *
     * @return $this
     */
    public function dropEdge($rid)
    {
        $this->dropRecord($rid); //returns RecordDelete|bool
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
        return $this->client->query($command);
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
        return $this->client->query($query->getScript());
    }

    /**
     * Map a raw result to the Spider Response
     * @param $results
     * @return Graph
     */
    public function mapToSpiderResponse($results)
    {
        // Map collection of results to graph if needed
        if (is_array($results)) {
            $newResults = [];
            foreach ($results as $result) {
                if ($result instanceof Record) {
                    $newResults[] = $this->recordToGraph($result);
                } else {
                    $newResults[] = $result;
                }
            }

            return new Graph($newResults);
        }

        // Map a single record to graph
        $record = $this->recordToGraph($results);

        // Fire it back
        return $record;
    }

    /**
     * Transforms string RID into object RID
     * @param string $rid
     * @return ID
     */
    protected function parseRid($rid)
    {
        if ($rid instanceof ID) {
            return $rid;
        } elseif (is_string($rid)) {
            $rid = trim($rid, "#");
            $pieces = explode(':', $rid);
            $cluster = $pieces[0];
            $position = $pieces[1];

            return new ID($cluster, $position);
        } else {
            throw new Exception("Not a valid ID");
        }
    }

    /**
     * Transforms RID object to RID string
     * @param $rid
     * @return string
     */
    protected function parseRidToString($rid)
    {
        if ($rid instanceof ID) {
            return "#$rid->cluster:$rid->position";
        } elseif (is_string($rid)) {
            return $rid;
        } else {
            throw new Exception("Not a valid ID");
        }
    }

    /**
     * Separates orient CLASS from properties or returns default
     * @param array $properties
     * @param string $default
     *
     * @return array
     */
    protected function parseOClass($properties, $default = 'V')
    {
        if (isset($properties['class'])) {
            $recordClass = $properties['class'];
            unset($properties['class']);
            return array($properties, $recordClass);
        } else {
            $recordClass = $default;
            return array($properties, $recordClass);
        }
    }

    /**
     * Builds a Record Object from properties
     * @param         $properties
     * @param         $recordClass
     * @param bool|ID $rid
     *
     * @return Record
     * @internal param $id
     */
    protected function buildRecord($properties, $recordClass, ID $rid = null)
    {
        if (is_null($rid)) {
            $recordId = new ID(9);
        } else {
            $recordId = $rid;
        }

        $record = (new Record())
            ->setOData($properties)
            ->setOClass($recordClass)
            ->setRid($recordId);
        return $record;
    }

    /**
     * Updates a record
     * @param $rid
     * @param $properties
     *
     * @return Record|\PhpOrient\Protocols\Binary\Operations\RecordUpdate
     */
    protected function updateRecord($rid, $properties)
    {
        $id = $this->parseRid($rid);
        list($properties, $recordClass) = $this->parseOClass($properties);

        $updatedRecord = $this->buildRecord($properties, $recordClass, $id);

        return $this->client->recordUpdate($updatedRecord);
    }

    /**
     * Deletes a record
     * @param $rid
     *
     * @return bool|\PhpOrient\Protocols\Binary\Operations\RecordDelete
     */
    protected function dropRecord($rid)
    {
        $id = $this->parseRid($rid);

        $delete = $this->client->recordDelete($id);
        return $delete;
    }

    /**
     * Retrieves a record
     * @param $rid
     *
     * @return mixed
     */
    protected function getRecord($rid)
    {
        if (is_array($rid)) {
            // Do it through SQL
        }
        return $this->client->recordLoad($this->parseRid($rid))[0];
    }

    /**
     * Map a Record to a Graph, preserving metadata
     * @param $results
     * @return Graph
     */
    protected function recordToGraph($results)
    {
        $record = new Graph($results->getOData());
        $record->add([
            'id' => $results->getRid()->jsonSerialize(),
            'rid' => $results->getRid(),
            'version' => $results->getVersion(),
            'oClass' => $results->getOClass(),
        ]);
        return $record;
    }
}
