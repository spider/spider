<?php
namespace Michaels\Spider\Test\Stubs\FirstDriverStub;

use Michaels\Spider\Commands\CommandInterface;
use Michaels\Spider\Drivers\AbstractDriver;
use Michaels\Spider\Drivers\DriverInterface;
use Michaels\Spider\Graphs\Record;

class Driver extends AbstractDriver implements DriverInterface
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
     * @return $this
     */
    public function open()
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
     * @param CommandInterface $query
     * @return array|Record|\Michaels\Spider\Drivers\Graph
     */
    public function executeReadCommand(CommandInterface $query)
    {
        return $this->returnData();
    }

    /**
     * Executes a write command
     *
     * These are the "CUD" in CRUD
     *
     * @param CommandInterface $command
     * @return \Michaels\Spider\Drivers\Graph|Record|array|mixed mixed values for some write commands
     */
    public function executeWriteCommand(CommandInterface $command)
    {
        return $this->returnData();
    }

    /**
     * Executes a read command without waiting for a response
     *
     * @param CommandInterface $command
     * @return $this
     */
    public function runReadCommand(CommandInterface $command)
    {
        return $this;
    }

    /**
     * Executes a write command without waiting for a response
     *
     * @param CommandInterface $query
     * @return $this
     */
    public function runWriteCommand(CommandInterface $query)
    {
        return $this;
    }
}
