<?php
namespace Michaels\Spider\Test\Stubs\FirstDriverStub;

use Michaels\Spider\Drivers\DriverInterface;
use Michaels\Spider\Graphs\Record;
use Michaels\Spider\Queries\QueryInterface;

class Driver implements DriverInterface
{

    protected function returnData()
    {
        return new Record([
            'one' => 1,
            'two' => true,
            'three' => 'three',
        ]);
    }

    /**
     * Connect to the database
     *
     * @param array $credentials credentials
     * @param array $config
     * @return $this
     */
    public function open(array $credentials, array $config = [])
    {
        return $this;
    }

    /**
     * Close the database connection
     * @return $this
     */
    public function close()
    {
        return $this;
    }

    /**
     * Executes a Query or read command
     *
     * This is the R in CRUD
     *
     * @param QueryInterface $query
     * @return array|Record|\Michaels\Spider\Drivers\Graph
     */
    public function executeReadCommand(QueryInterface $query)
    {
        return $this->returnData();
    }

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param QueryInterface $query
     * @return \Michaels\Spider\Drivers\Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(QueryInterface $query)
    {
        return $this->returnData();
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param QueryInterface $query
     * @return $this
     */
    public function runReadCommand(QueryInterface $query)
    {
        return $this;
    }

    /**
     * Executes a write command without waiting for a response
     *
     * @param QueryInterface $query
     * @return $this
     */
    public function runWriteCommand(QueryInterface $query)
    {
        return $this;
    }
}
