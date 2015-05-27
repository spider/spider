<?php
namespace Michaels\Spider\Drivers\OrientDB;

use Michaels\Spider\Drivers\DriverInterface;
use PhpOrient\PhpOrient;
use PhpOrient\Protocols\Binary\Data\ID;
use PhpOrient\Protocols\Binary\Data\Record;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Class OrientDriver
 * @package Michaels\Spider\Drivers\OrientDB
 */
class OrientDriver implements DriverInterface
{

    public function __construct()
    {
        $this->client = new PhpOrient();
    }

    public function connect($properties)
    {
        $this->client->configure($properties->getAll());
        $this->client->connect();
    }

    public function createDb($name, $storageType = null, $databaseType = null)
    {
        return $this->client->dbCreate($name, $storageType, $databaseType);
    }

    public function dropDb($name)
    {
        return $this->client->dbDrop($name);
    }

    public function dbExists($name)
    {
        return $this->client->dbExists($name);
    }

    public function listDbs()
    {
        return $this->client->dbList();
    }

    public function openDb($database)
    {
        return $this->client->dbOpen($database);
    }

    public function closeDb()
    {
        return $this->client->dbClose();
    }

    public function statement($statement)
    {
        return $this->client->query($statement);
    }

    public function query($statement)
    {
        return $this->client->query($statement);
    }

    /**
     * Add a vertex (node)
     * For OrientDB, you can use $properties['class'] to utilize native class functionality
     *
     * @param $properties
     *
     * @return Record|\PhpOrient\Protocols\Binary\Operations\RecordCreate
     */
    public function addVertex($properties)
    {
        list($properties, $recordClass) = $this->parseOClass($properties);

        $record = $this->buildRecord($properties, $recordClass);
        $result = $this->client->recordCreate($record);

        return $result;
    }

    public function getVertex($rid)
    {
        return $this->getRecord($rid);
    }

    public function updateVertex($rid, $properties)
    {
        return $this->updateRecord($rid, $properties);
    }

    public function dropVertex($rid)
    {
        return $this->dropRecord($rid);
    }

    public function addEdge($from, $to, $properties)
    {
        $from = $this->parseRidToString($from);
        $to = $this->parseRidToString($to);
        list($properties, $class) = $this->parseOClass($properties, 'E');

        $statement = "create edge $class from $from to $to content " . json_encode($properties);

        return $this->client->command($statement);
    }

    public function getEdge($rid)
    {
        return $this->getRecord($rid);
    }

    public function updateEdge($rid, $properties)
    {
        return $this->updateRecord($rid, $properties);
    }

    public function dropEdge($rid)
    {
        return $this->dropRecord($rid);
    }

    /**
     * @param $rid
     *
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
     * @param        $properties
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
}
